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
        //exit('stop');
        foreach (Configure::read('CITY_IN_SOUTH') as $key => $value) {
            $this->getDataSouth($value['code'], $value['slug'], $value['w']);
        }

        $this->getDataNorth();exit('ok');
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
        $endDate = date('H', strtotime('+7 hour')) > 18 ? 1 : 0;

        //Init variable
        $http = new Client();
        $begin = new \DateTime($newestDate);
        $end = new \DateTime();
        $end->modify("+$endDate day");     

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
        $endDate = date('H', strtotime('+7 hour')) > 18 ? 1 : 0;

        //Init variable
        $http = new Client();
        $begin = new \DateTime($newestDate);
        $end = new \DateTime();
        $end->modify("+$endDate day");

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

    private function _getWanting() {
        $commandsTable = TableRegistry::get('Commands');
        $commands = $commandsTable->find('all', [
                        'order' => ['date_command' => 'DESC']
                    ]);

        return $commands;
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
        $commandDate = new \DateTime('2015-06-25');

        // Get previous command
        $prevCommand = $commandsTable->find('all', [
                        'conditions' => ['date_command <' => $commandDate->format('Ymd')],
                        'order' => ['date_command' => 'DESC']
                    ])->first();

        // Get space from previous command
        $prevDate   = $prevCommand ? new \DateTime($prevCommand->date_command->i18nFormat('yyyy-MM-dd')) : $commandDate;
        $space      = $prevDate->diff($commandDate)->format("%a");

        /**
         * Create command
         *
         */
        // Get command exist
        $command = $id ? $commandsTable->get($id) : $commandsTable->newEntity();

        // Process command
        if ($this->request->is('post')) {
            $exeDate = implode('-', $this->request->data['date_command']);            
            $checkCommand = $command->isNew() ? $commandsTable->findByDateCommand($exeDate)->first() : $command;
            $command = $checkCommand ? $checkCommand : $command;
            $command = $commandsTable->patchEntity($command, $this->request->data);

            $command->wanting = Configure::read('COMMAND.WIN_ON_DAY');
            $wantingOfSpace = $space > 0 ? $command->wanting * $space : 0;
            $command->wanting_increase = Configure::read('COMMAND.WIN_ON_DAY') + $wantingOfSpace;

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
var_dump($prevCommand);
var_dump($command);exit;

            if ($commandsTable->save($command)) {
                $this->Flash->success(__('Lập lệnh thành công'));
                return $this->redirect(['action' => 'updateWanting']);
            }
            $this->Flash->error(__('Lập lệnh thất bại'));
        }

        $this->set('command', $command);
        $this->set('commands', $this->_getWanting());
    }
}
