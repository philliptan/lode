<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\Network\Http\Client;
use Cake\Collection\Collection;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\I18n\Time;
use Cake\Validation\Validation;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Flash'); // Include the FlashComponent
    }

    /**
     * Displays a view
     *
     * @return void|\Cake\Network\Response
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function display()
    {
        echo phpinfo();exit;
        $path = func_get_args();

        $count = count($path);
        if (!$count) {
            return $this->redirect('/');
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        try {
            $this->render(implode('/', $path));
        } catch (MissingTemplateException $e) {
            if (Configure::read('debug')) {
                throw $e;
            }
            throw new NotFoundException();
        }
    }

    public function index() {
        $this->getDataNorth();
        foreach (Configure::read('CITY_IN_SOUTH') as $key => $value) {
            $this->getDataSouth($value['code'], $value['slug'], $value['w']);
        }
        exit('ok');
    }

    public function getDataNorth() {
        // Init table
        $resultsTable = TableRegistry::get('Results');
        $query = $resultsTable->find('all', [
                    'fields' => ['date_result'],
                    'conditions' => ['area' => Configure::read('Area.north.code')],
                    'order' => ['date_result' => 'DESC']
                ]);

        $newestDate = $query->first()->date_result->modify('+1 days')->i18nFormat('YYYY-MM-dd');
        $endDate = date('H', strtotime('+7 hour')) > 18 ? 0 : 1;

        //Init variable
        $http = new Client();
        $begin = new \DateTime($newestDate);
        $end = new \DateTime();
        $end->modify("-$endDate day");     

        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($begin, $interval ,$end);

        // Get list city
        $collection = new Collection(Configure::read('City'));
        $arrCity = $collection->combine('w', 'code')->toArray();

        foreach($daterange as $date){
            $dateFormat = $date->format("d-m-Y");
            $wday = $date->format("w");
            $dateResult = $date->format("Ymd");

            $url = "http://www.xoso.net/getkqxs/mien-bac/{$dateFormat}.js";
            $response = $http->get($url);

            preg_match_all("|'(.*)'|", $response->body(), $match);
            $dom = new \DOMDocument;
            $dom->loadHTML($match[1][2]);
            foreach( $dom->getElementsByTagName('td') as $node)
            {
                $class = $node->getAttribute('class');
                $value = preg_replace('/\s+/', '', $node->nodeValue);
                if (preg_match('/^giai(\d|db)+$/', $class) && $value !== '') {
                    $arrContent = explode('-', $value);
                    foreach($arrContent as $content) {
                        $result = $resultsTable->newEntity();
                        $result->date_result = $dateResult;
                        $result->level = Configure::read("Result_Level.$class");
                        $result->content = $content;
                        $result->area = 1;
                        $result->city = $arrCity[$wday];
                        $result->created_date = $end->format("YmdHis");
                        $result->modified_date = $end->format("YmdHis");
                        $resultsTable->save($result);
                    }                    
                }            
            }
        }
    }

    public function getDataSouth($city, $slug, $arrWeekDay = array()) {
        $area = Configure::read('Area.south.code');
        $resultsTable = TableRegistry::get('Results');
        $query = $resultsTable->find('all', [
                    'fields' => ['date_result'],
                    'conditions' => ['area' => $area, 'city' => $city],
                    'order' => ['date_result' => 'DESC']
                ]);

        $dataFirst = $query->first();
        $newestDate = $dataFirst ? $dataFirst->date_result->modify('+1 days')->i18nFormat('YYYY-MM-dd') : '2008-01-01';
        $endDate = date('H', strtotime('+7 hour')) > 18 ? 0 : 1;

        //Init variable
        $http = new Client();
        $begin = new \DateTime($newestDate);
        $end = new \DateTime();
        $end->modify("-$endDate day");

        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($begin, $interval ,$end);

        foreach($daterange as $date){
            $dateFormat = $date->format("d-m-Y");
            $wday = $date->format("w");
            $dateResult = $date->format("Ymd");

            if (!in_array($wday, $arrWeekDay)) {
                continue;
            }
            $this->log($dateResult, 'info');

            $url = "http://www.xoso.net/getkqxs/$slug/{$dateFormat}.js";
            $response = $http->get($url);

            preg_match_all("|'(.*)'|", $response->body(), $match);
            $dom = new \DOMDocument;
            $dom->loadHTML($match[1][2]);
            foreach( $dom->getElementsByTagName('td') as $node)
            {
                $class = $node->getAttribute('class');
                $value = preg_replace('/\s+/', '', $node->nodeValue);
                if (preg_match('/^giai(\d|db)+$/', $class) && $value !== '') {
                    $arrContent = explode('-', $value);
                    foreach($arrContent as $content) {
                        $result = $resultsTable->newEntity();
                        $result->date_result = $dateResult;
                        $result->level = Configure::read("Result_Level.$class");
                        $result->content = $content;
                        $result->area = $area;
                        $result->city = $city;
                        $result->created_date = $end->format("YmdHis");
                        $result->modified_date = $end->format("YmdHis");
                        $resultsTable->save($result);
                    }                    
                }            
            }
        }
    }

    public function countXX($area = 1, $greater = 3) {
        // Get count day
        $resultsTable = TableRegistry::get('Results');
        $totalDay = $resultsTable->find('all')
                    ->select(['date_result'])
                    ->where(['area' => $area])
                    ->group('date_result')
                    ->count();

        $result = [];
        for ($i=0; $i < 10; $i++) { 
            $result[$i] = $this->processMax($i, $area, $greater);
        }

        $this->set('count', $result);
        $this->set('day', $totalDay);
    }

    public function processMax($one, $area, $greater) {
        // Init table
        $resultsTable = TableRegistry::get('Results');

        $query = $resultsTable->find('all')->where(['area' => $area, "RIGHT(content, 1) = $one"]);

        $count = $query->func()->count([
            'date_result' => 'literal',
        ]);

        $oneEnd = $query->func()->right([
            'content' => 'literal',
            "1",
        ]);

        $query->select([
            'date_result', 
            'count' => $count,
            'one_end' => $oneEnd,
        ])
        ->group(['date_result'])
        ->having(['count >' => $greater])
        ->order(['date_result' => 'ASC']);

        $result = [];
        $arrCheck = [];
        foreach ($query as $key => $row) {
            $dateFormat = $row->date_result;
            $dateFormat = $dateFormat->i18nFormat('yyyy-MM-dd');

            $lastDate = $arrCheck ? $arrCheck[count($arrCheck) - 1] : $dateFormat;

            $date1 = new \DateTime($lastDate);
            $date2 = new \DateTime($dateFormat);

            $space = $date1->diff($date2)->format("%a");
            $space = $space ? $space - 1 : $space;
//echo "<br>x$one $space : " . $date1->format("Y-m-d") .' --- '. $date2->format("Y-m-d");
            $result[$space]['count'] = Hash::check($result, "$space.count") ? ($result[$space]['count'] + 1) : 1;
            $result[$space]['index'] = $space;
            $arrCheck[] = $dateFormat;
        }
        ksort($result);

        return $result;
    }

    public function processCount($oneEnd, $query) {
        $countTotalOnDay = [];
        foreach ($query as $key => $row) {
            $dateFormat = $row->date_result->i18nFormat('yyyy-MM-dd');
            if ($row->one_end == $oneEnd) {
                $countTotalOnDay[$dateFormat] = Hash::check($countTotalOnDay, $dateFormat) ? ($countTotalOnDay[$dateFormat] + 1) : 1;
            }
        }

        $arrDurationPresent = [];
        $space = 0;
        foreach ($countTotalOnDay as $key => $value) {
            if ($value > 3) {
                
            }
            else {
                $space++;
                continue;
            }
                
            $arrDurationPresent[$space]['count'] = Hash::check($arrDurationPresent, "$space.count") ? ($arrDurationPresent[$space]['count'] + 1) : 1;
            //$arrDurationPresent[$space]['date'][] = $key;
            $arrDurationPresent[$space]['index'] = $space;
            $space = 0;
        }
        
        $arrDurationPresent = Hash::sort($arrDurationPresent, '{n}.index', 'asc');
        return $arrDurationPresent;
    }

    public function nearly($area = 1) {
        $result = [];
        $result = $this->processNearlyGt3(0, $result, $area);

        $greater = $area == 1 ? 3 : 4;
        $this->set('nearly', $result);
        $this->set('greater', $greater);
    }

    public function processNearlyGt3($oneEnd, $result, $area) {
        // Init table
        $resultsTable = TableRegistry::get('Results');
        $query = $resultsTable->find('all');
        $countGT3 = $query->func()->count([
            'content' => 'literal',
        ]);
        $one = $query->func()->right([
            'content' => 'literal',
            "1",
        ]);
        $query->select([
            'date_result',
            'countgt3' => $countGT3,
            'one_end' => $one
        ])
        ->where(['area' => $area])
        ->group(['date_result', 'RIGHT(content, 1)'])
        ->order(['date_result' => 'DESC']);

        foreach ($query as $key => $row) {
            $dateFormat = $row->date_result->i18nFormat('yyyy-MM-dd');
            $result[$dateFormat][$row->one_end] = $row->countgt3;
        }

        return $result;
    }

    private function _getWanting($toArray = false, $limit = 60) {
        $commandsTable = TableRegistry::get('Commands');
        $commands = $commandsTable->find('all', [
                        'order' => ['date_command' => 'DESC'],
                        'limit' => $limit,
                    ]);

        // Set index is date_command
        $combined = (new Collection($commands))->combine(
                    function ($entity) { return $entity->date_command->i18nFormat('yyyy-MM-dd'); },
                    function ($entity) { return $entity; }
                );

        return $toArray ? $combined->toArray() : $combined;
    }

    public function wanting($area=1) {
        $this->set('commands', $this->_getWanting());
    }

    public function updateWanting($id=null) {
        // Init
        $greater = 3;
        $winMin = Configure::read('COMMAND.WIN_MIN_NORTH');
        $rotioLose = Configure::read('RATIO.LOSE_NORTH');
        $rotioWin = Configure::read('RATIO.WIN');
        $numberOfWin = 1;
        $xx = 7;

        // Init table
        $commandsTable = TableRegistry::get('Commands');

        // Prepare data
        $exeDate = $this->request->is('post') ? implode('-', $this->request->data['date_command']) : '+7 hour';
        $commandDate = new \DateTime($exeDate);

        /**
         * Create command
         *
         */
        // Get command exist
        $command = $id ? $commandsTable->get($id) : $commandsTable->newEntity();

        // Process command
        if ($this->request->is('post')) {
            // Get previous command
            $prevCommand = $commandsTable->find('all', [
                            'conditions' => ['date_command <' => $commandDate->format('Ymd')],
                            'order' => ['date_command' => 'DESC']
                        ])->first();

            // Get space from previous command
            $prevDate   = $prevCommand ? new \DateTime($prevCommand->date_command->i18nFormat('yyyy-MM-dd')) : $commandDate;
            $space      = $prevDate->diff($commandDate)->format("%a");

            $checkCommand = $command->isNew() ? $commandsTable->findByDateCommand($commandDate->format('Ymd'))->first() : $command;
            $command = $checkCommand ? $checkCommand : $command;
            $command = $commandsTable->patchEntity($command, $this->request->data);

            $command->wanting = Configure::read('COMMAND.WIN_ON_DAY');
            $command->wanting_increase = Configure::read('COMMAND.WIN_ON_DAY');

            $command->prev_profit = 0;
            $command->prev_profit_increase = 0;
            $command->modified = $commandDate->format('YmdHis');

            if ($prevCommand && $prevCommand->number_win > $greater) {
            }
            else if ($prevCommand && $prevCommand->number_win <= $greater) {
                $command->wanting_increase += $prevCommand->wanting_increase;
                $command->prev_profit = ($prevCommand->revenue - $prevCommand->investment);
                $command->prev_profit_increase = $command->prev_profit + $prevCommand->prev_profit_increase;
            }

            $wantingReal = $prevCommand ? ($command->wanting_increase - $command->prev_profit_increase) : $command->wanting;

            $command->money_on_one = $wantingReal / ($rotioWin * $winMin - $rotioLose);
            $command->investment = $command->money_on_one * $rotioLose;
            $command->revenue = $command->number_win * $command->money_on_one * Configure::read('RATIO.WIN');
            $profit = $command->revenue - $command->investment;

            if ($command->isNew()) {
                //$command->date_command = $commandDate->format('Ymd');
                $command->created = $commandDate->format('YmdHis');
            }

            if ($commandsTable->save($command)) {
                $this->Flash->success(__('Lập lệnh thành công'));
                return $this->redirect(['action' => 'updateWanting']);
            }
            $this->Flash->error(__('Lập lệnh thất bại'));
        }

        $this->set();
        $this->set('command', $command);
        $this->set('commands', $this->_prepareWantingView($commandDate));
    }

    private function _prepareWantingView($commandDate) {
        // Init table
        $commandsTable = TableRegistry::get('Commands');

        // Get first command
        $firstCommand = $commandsTable->find('all', [
                        'order' => ['date_command' => 'ASC']
                    ])->first();
        $spaceFirst = (new \DateTime($firstCommand->date_command->i18nFormat('yyyy-MM-dd')))->diff($commandDate)->format("%a");

        // Set date range
        $limit = 60;
        $limit = $spaceFirst < $limit ? $spaceFirst : $limit;
        $begin = new \DateTime($commandDate->format('Y-m-d'));
        $begin->modify("-$limit day");

        $interval = new \DateInterval('P1D');
        $datePeriod = new \DatePeriod($begin, $interval , $commandDate);

        $commands = array();
        $commandList = $this->_getWanting(true, $limit);

        $prevCommand = NULL;
        foreach ($datePeriod as $key => $value) {
            $dateFormat = $value->format('Y-m-d');

            if (isset($commandList[$dateFormat])) {
                $commandTmp = $commandList[$dateFormat];
                goto prev_command;
            }

            $commandTmp = $commandsTable->newEntity();
            $commandTmp->wanting = Configure::read('COMMAND.WIN_ON_DAY');
            $commandTmp->wanting_increase = Configure::read('COMMAND.WIN_ON_DAY');
            $commandTmp->date_command = new Time($dateFormat);

            if ($commandTmp->isNew() && $prevCommand && $prevCommand->isNew()) {
                $commandTmp->wanting_increase = $commandTmp->wanting + $prevCommand->wanting_increase;
            }
            else if ($commandTmp->isNew() && $prevCommand && !$prevCommand->isNew()) {
                $commandTmp->wanting_increase = $commandTmp->wanting;
            }

            prev_command:
            $commands[$dateFormat] = $commandTmp;
            $prevCommand = $commandTmp;
        }

        krsort($commands);

        return $commands;
    }

    public function southFour() {
        // Init table
        $resultsTable = TableRegistry::get('Results');

        // Prepare
        $year = Hash::get($this->request->query, 'search_year');
        $month = Hash::get($this->request->query, 'search_month');
        $head = Hash::get($this->request->query, 'search_head');
        $trail = Hash::get($this->request->query, 'search_trail');
        $dateFormat = [];
        $dateFormatValue = [];
        $searchYearMonth = NULL;
        $searchHeadTrail = [];
        $conditions = [
            'area' => Configure::read('Area.south.code'), 
            'level IN' => [1, 9],
        ];

        // Setting get data by year, month
        if (Validation::notBlank($year)) {
            $dateFormat[] = '%Y';
            $dateFormatValue[] = $year;
        }
        if (Validation::notBlank($month)) {
            $dateFormat[] = '%m';
            $dateFormatValue[] = $month;
        }
        if (Validation::notBlank($year) || Validation::notBlank($month)) {
            $conditions[] = "DATE_FORMAT(date_result, '". implode('', $dateFormat) ."') = " . implode('', $dateFormatValue);
        }

        // Setting get data by head, trail
        if (Validation::notBlank($head)) {
            $conditions[] = "MID(content, -2, 1) = $head";
        }
        if (Validation::notBlank($trail)) {
            $conditions[] = "MID(content, -1) = $trail";
        }

        $query = $resultsTable->find('all');

        $trailTwo = $query->func()->mid([
            'content' => 'literal',
            '-2'
        ]);

        $query->select([
            'id',
            'date_result',
            'trail' => $trailTwo,
            'city',
            'level',
        ])
        ->where($conditions)
        ->order(['date_result' => 'DESC', 'city' => 'ASC', 'level' => 'DESC']);
//debug($query);
        $this->set('trails', $query);
    }
}
