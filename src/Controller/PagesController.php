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
        $endDate = date('H', strtotime('+7 hour')) > 18 ? 0 : 1;

        //Init variable
        $http = new Client();
        $begin = new \DateTime($newestDate);
        $end = new \DateTime("-$endDate day");        

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
        $newestDate = '2008-03-18';//$dataFirst ? $dataFirst->date_result->modify('+1 days')->i18nFormat('YYYY-MM-dd') : '2008-01-01';
        //$endDate = date('H', strtotime('+7 hour')) > 18 ? 0 : 1;

        //Init variable
        $http = new Client();
        $begin = new \DateTime($newestDate);
        $end = new \DateTime($newestDate);
        //$end->modify("-$endDate day");   

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

            $url = "http://www.xoso.net/getkqxs/$slug/{$dateFormat}.js";var_dump($url);
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

            $space = $date1->diff($date2)->format("%d");
            $space = $space ? $space - 1 : $space;

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

        $this->set('nearly', $result);
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

    public function wanting() {
        // Init table
        $dataTmp = [
            '2015-06-22' => [
                'number_win' => 5,
                'kv_gop' => 300
            ],
            '2015-06-23' => [
                'number_win' => 4,
                'kv_gop' => 300
            ],
            '2015-06-24' => [
                'number_win' => 2,
                'kv_gop' => 300
            ],
            '2015-06-25' => [
                'number_win' => 1,
                'kv_gop' => 300
            ],
            '2015-06-26' => [
                'number_win' => 3,
                'kv_gop' => 300
            ],
            '2015-06-27' => [
                'number_win' => 2,
                'kv_gop' => 300
            ],
            '2015-06-28' => [
                'number_win' => 6,
                'kv_gop' => 300
            ],
            '2015-06-29' => [
                'number_win' => 3,
                'kv_gop' => 300
            ],
            '2015-06-30' => [
                'number_win' => 5,
                'kv_gop' => 300
            ],
        ];

        $wantingMoney = 300;
        $win = 73;
        $lose = 10*27*0.79;
        $result = [];

        foreach ($dataTmp as $key => $value) {
            $date = new \DateTime($key);
            $prevDate = "";
            if ($result) {
                $date->modify('-1 days');
                $prevDate = $date->format('Y-m-d');
            }
            $kvGop = $prevDate ? $result[$prevDate]['kv_gop'] : $wantingMoney;
            $hwa_bu = 0;
            $bu_gop = 0;
            if ($prevDate && $result[$prevDate]['number_win'] > 3) {
                $kvGop = $wantingMoney;
                $hwa_bu = 0;
                $bu_gop = 0;
            }
            else if ($prevDate && $result[$prevDate]['number_win'] <= 3) {
                $kvGop = $kvGop + $wantingMoney;
                $hwa_bu = $result[$prevDate]['loi_nhuan'];
                $bu_gop = $hwa_bu + $result[$prevDate]['bu_gop'];
            }

            $kv_real = $prevDate ? ($kvGop - $bu_gop) : $wantingMoney;

            $moneyOnOne = $kv_real/(4*$win-$lose);
            $von = $moneyOnOne * $lose;
            $doanh_thu = $value['number_win'] * $moneyOnOne * $win;
            $loi_nhuan = $doanh_thu - $von;

            $result[$key] = [
                'kv'        => $wantingMoney,
                'kv_gop'    => $kvGop,
                'hwa_bu'    => $hwa_bu,
                'bu_gop'    => $bu_gop,
                'xx'        => 'XX',
                'dau_tu'     => $moneyOnOne,
                'number_win' => $value['number_win'],
                'von'        => $von,
                'doanh_thu'       => $doanh_thu,
                'loi_nhuan'  => $loi_nhuan,
                'kv_real'    => $kv_real,
            ];
        }

        $this->set('result', $result);
    }
}
