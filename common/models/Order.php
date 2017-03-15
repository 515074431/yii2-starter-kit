<?php
namespace common\models;

use yii\db\ActiveRecord;
use common\lib\VRUAN;
use common\models\soccer\Ticket;
use yii\helpers\ArrayHelper;

/**
 * Class Order
 * @package common\models
 * @property $status 状态
 * @property $betcode 赌注
 * @property $times 倍数
 */
class Order extends ActiveRecord
{
    const STATUS_DELETED = 0;//订单软删除
    const STATUS_CANCEL = 30;//订单取消
    const STATUS_TO_BE_PAID = 5; //待支付
    const STATUS_BOOKING = 10;//已支付，待出票
    const STATUS_BOOKED = 15;//已经出票，等待开奖
    const STATUS_WINNING_YES = 20;//订单中奖
    const STATUS_WINNING_NO = 25;//订单未中奖

    public function status(){
        if($this->status == self::STATUS_TO_BE_PAID){
            //如果订单状态是待支付，并且时间过去了15分钟，就将状态改为取消！
            if(($this->created_at + \Yii::$app->params['order.expire'])<time())
                $this->cancel();
        }
        return $this->status ;
    }
    public function status_info(){
        $arr =  [
            self::STATUS_DELETED => '订单已删除',
            self::STATUS_CANCEL => '订单取消',
            self::STATUS_TO_BE_PAID => '待支付',
            self::STATUS_BOOKING => '已支付，待出票',
            self::STATUS_BOOKED => '已出票，待开奖',
            self::STATUS_WINNING_YES => '订单中奖',
            self::STATUS_WINNING_NO => '订单未中奖',
        ];
        return $arr[$this->status()];
    }
    public static $orders=[];

    const TYPE_CODE_DLT = 1;
    const TYPE_CODE_JCZQ = 2;
    const TYPE_CODE_PL3 = 3;//排列3类型代码
    const TYPE_CODE_PL5 = 4;//排列5类型代码

    const TYPE_BUY_LIST_HIDE = 1;
    const TYPE_REWARD_LIST_HIDE = 2;

    const TOUZHU_CODE_PL3_ZHIXUAN = 1;//排列3 投注方式代码 直选
    const TOUZHU_CODE_PL3_ZUSAN = 2;//排列3 投注方式代码 组选三
    const TOUZHU_CODE_PL3_ZULIU = 3;//排列3 投注方式代码 组选六
    const TOUZHU_CODE_PL3 = [//排列3 投注方式代码
        1=>'直选',
        2=>'组选三',
        3=>'组选六'
    ];



    public  function attributes()
    {
        /*
         * 'odds_in_time' : 即时赔率,因为比赛赔率会一直变化，所以需要保存出票时的赔率
         * */
        return
        ArrayHelper::merge([
            'user_id',
            'user_name',
            'times',
            'type_code',
            'type_name',
            'type_id',
            'bonus',
            'price',
            'betcode',
            'period',
            'wincode',
            'buy_list_hide',
            'reward_list_hide',
            'new_bonus',
            'odds_in_time'//即时赔率
        ],parent::attributes());
    }
    public  function fill($params){
        foreach ($params as $k=>$param){
            if(in_array($k,$this->attributes()))
            $this->$k = $param ;
        }
        return $this;
    }
    public  function beforeSave($insert)
    {
        parent::beforeSave($insert);
        if($this->type_code == static::TYPE_CODE_DLT){
            $this->type_name = "大乐透";
        }
        if($this->type_code == static::TYPE_CODE_PL3){
            $this->type_name = "排列3";
        }
        if($this->type_code == static::TYPE_CODE_PL5){
            $this->type_name = "排列5";
        }
        if($this->type_code == static::TYPE_CODE_JCZQ){
            //如果订单是竞彩足球
            $this->type_name = "竞彩足球";
        }
        if($this->status == static::STATUS_TO_BE_PAID){
            $this->status_info = "订单待支付";
        }
        if($this->status == static::STATUS_BOOKING){
            $this->status_info = "已支付，待出票";
        }
        if($this->status == static::STATUS_BOOKED){
            $this->status_info = "已出票，待开奖";
        }
        if($this->status == static::STATUS_DELETED){
            $this->status_info = "订单已删除";
        }
        if($this->status == static::STATUS_CANCEL){
            $this->status_info = "订单被取消";
        }
        if($this->status == static::STATUS_WINNING_NO){
            $this->status_info = "未中奖";
        }
        if($this->status == static::STATUS_WINNING_YES){
            $this->status_info = "中奖";
        }
        if(!isset($this->price)){
            $this->fillPrice();
        }
        return true;
    }
    public  function getIsFootBall(){
        return ($this->type_code === static::TYPE_CODE_JCZQ);
    }
    public  function getIsWaitForOpen(){
        return $this->status === static::STATUS_BOOKED;
    }
    public function fillBonus(){
        //如果是竞彩足球⚽️，如果中奖，需要把订单总奖金算出来
        if(!$this->isFootBall) return $this;
        if(!isset($this->wincode)) return $this;
        $total_odd = 0 ;
        $this->bonus = 0;
        $results=[];
//        $match_ids = [];
        //58385452f53baf08ec6594f4:6-1|58385452f53baf08ec6594f2:6-3|5837e502fe39ed356015d674:6-3||2c1,3c1
        list($match_codes,$chuans) = explode('||',$this->wincode);
        $match_codes = explode('|',$match_codes);
        $chuans = explode(',',$chuans);
        foreach($chuans as $chuan){
            $results = ArrayHelper::merge($results,getCombinationToString($match_codes,$chuan[0],'|'));
        }
        foreach($results as $result){
            $r_odd = 1 ;
            //根据code获取赔率
            $matchcodes = explode('|',$result);
            foreach ($matchcodes as $matchcode){
                $m_odd = 0;
                list($match_id,$codes) = explode(':',$matchcode);
                $codes = explode(',',$codes);
                foreach ($codes as $code){
//                    $m_odd += Match::findById($match_id)->GetOddsByCode($code);
                    $m_odd += $this->GetOddsByCodeAndMatchId($match_id,$code);
                }
                $r_odd *= $m_odd ;
            }
            $total_odd+=$r_odd;
        }
        $this->bonus  = $total_odd*200*$this->times;
        return $this;
    }
    private function fillPrice(){
        if($this->type_code == static::TYPE_CODE_JCZQ){
            $zhu = getZhuFromBetCode($this->betcode);
            $this->price = $zhu*200*$this->times;
        }
        if($this->type_code == static::TYPE_CODE_DLT){
            $amount = 0 ;
            $bets = explode(';',$this->betcode);
            foreach ($bets as $bet){
                if(empty($bet)){continue;}
                $arr = explode(',',$bet);
                if(count($arr)!==2) VRUAN::error(WHRONG_BETCODE);
                $red = $arr[0];
                $red_ball = explode('-',$red);
                if(count($red_ball)<5) VRUAN::error(WHRONG_BETCODE);
                $n = count(getCombinationToString($red_ball,5));
                $blue = $arr[1];
                $blue_ball = explode('-',$blue);
                if(count($blue_ball)<2) VRUAN::error(WHRONG_BETCODE);
                $m = count(getCombinationToString($blue_ball,2));
                $amount += $n*$m;
            }
            $this->price = 200*$amount*$this->times;
        }
    }
    public function getMatches(){
        //如果是竞彩足球，获取比赛信息
        if(!$this->getIsFootBall()) return [];
        $matches = [];
        list($match_codes,$chuans) = explode('||',$this->betcode);
        $match_codes = explode('|',$match_codes);
        foreach ($match_codes as $match_code){
            list($match_id,$codes) = explode(':',$match_code);
            $match = Match::findById($match_id);
            $codes = explode(',',$codes);
            if($match === null) continue;
            $matches[] = [
                'match_id' => $match->getId(),
                'num' => $match->num,
                'home_team_name' => $match->home_team_name(),
                'guest_team_name' => $match->guest_team_name(),
                'final' => $match->final,
                'win_codes'=>$match->WinCodes(),
                'bet_codes'=>$this->BetCodes($match_id),
                'start_time'=>$match->start_time,
                'rq'=>$match->rq,
            ] ;
        }
        return $matches ;
    }

    public function getTickets(){
        $result = [];
        $tickets = Ticket::find()->where(['order_id'=>$this->getId()])->all();
        foreach ($tickets as $ticket){
            $result[]=[
                'ticket_id'=>$ticket->id,
                'chain'=>$ticket->chain,
                'price'=>$ticket->price,
                'bonus'=>$ticket->bonus,
                'status'=>Order::findById($ticket->order_id)->status(),
                'status_info'=>$ticket->status_info(),
                'times'=>$ticket->times,
                'zhu'=>getZhuFromBetCode($ticket->bet_code),
                'items'=>$ticket->items(),
            ];
        }
        return $result;
    }

    /*
     * 根据比赛id获取某个订单某场比赛的betcodes
     * */
    public function BetCodes($match_id){
        //某个订单根据比赛编号获取比赛赔率
        //如果未出票时，订单是没有记录赔率的，所以暂时返回即时赔率

        if(!isset($this->odds_in_time)) return Match::findById($match_id)->BetCodes($this->GetCodesByMatchId($match_id));
        $in_time_arr = json_decode($this->odds_in_time,true) ;
        if(!isset($in_time_arr[$match_id])) return Match::findById($match_id)->BetCodes($this->GetCodesByMatchId($match_id));
        $ret=null;
        foreach ($in_time_arr[$match_id] as $k =>$v){
            $ret[] = [
                'code'=>$k,
                'odd'=>$v
            ];
        }
        return $ret??Match::findById($match_id)->BetCodes($this->GetCodesByMatchId($match_id));
    }
    public function GetOddsByCodeAndMatchId($match_id,$code){
            $arrs = $this->BetCodes($match_id);
            foreach ($arrs as $arr){
                if($arr['code'] == $code) {
                    return $arr['odd'];
                }
            }
            return 1;
    }
    public function getWinningCode(){
        //获取大乐透的开奖结果
        if($this->type_code == static::TYPE_CODE_DLT){
            $dlt_result = DltResult::findOne(['period'=>(string)$this->period]);
            if($dlt_result!==null&&isset($dlt_result->code)){
                return $dlt_result->code;
            }
            return null;
        }
        if($this->type_code == static::TYPE_CODE_JCZQ) {
            $wincode = [];
            $winchuans = [];
            list($match_codes,$chuans) = explode('||',$this->betcode);
            $chuans = explode(',',$chuans);
            //需要最小中奖比赛数
            $min_open_count = 8 ;
            foreach($chuans as $chuan){
                if($min_open_count > $chuan[0]){
                    $min_open_count = $chuan[0];
                }
            }

            $match_codes = explode('|',$match_codes);
            foreach ($match_codes as $match_code){
                list($match_id,$codes) = explode(':',$match_code);
                $codes = explode(',',$codes);
                $match = Match::findById($match_id);
                if(!$match->isEnd) return false;//比赛是否完结
                foreach ($codes as $k => &$code) {
                    if(!in_array($code,$match->WinCodes())) unset($codes[$k]);
                }
                if(count($codes)>0){
                    $wincode[] = $match_id.':'.implode(',',$codes);
                }
            }

            if (count($wincode)>=$min_open_count){
                foreach($chuans as $chuan){
                    if(count($wincode)>=$chuan){
                        $winchuans[]=$chuan;
                    }
                }
                $this->wincode = implode('|',$wincode)."||".implode(',',$winchuans);
            }
            return $this->wincode;
        }
        return null;
    }
    public function cancel(){
        $this->status = static::STATUS_CANCEL;
        $o = static::findOne($this->id);
        $o->status = static::STATUS_CANCEL;
        $o->save();
        return $this;
    }
    public function hide($type){
        if($type == self::TYPE_BUY_LIST_HIDE){
            $o = static::findOne($this->id);
            $o->buy_list_hide = 1;
            return $o->save();
        }
        if($type == self::TYPE_REWARD_LIST_HIDE){
            $o = static::findOne($this->id);
            $o->reward_list_hide = 1;
            return $o->save();
        }
        return false;
    }

    public function user_name(){
        return \username($this->user_id);
    }
    public function bonus(){
        //如果中奖，但是中奖金额为0，则重新计算
        if($this->status() == Order::STATUS_WINNING_YES && empty($this->bonus)){
                return $this->fillBonus()->bonus;
        }elseif($this->status() == Order::STATUS_WINNING_YES){
            return $this->bonus;
        }
        return null;
    }

    /*
     * 出票时用来设置即时赔率的
     * 比赛id => [code => odd]
     * */
    public function save_odds_in_time(){
//        $this->odds_in_time
        $in_time_arr = [];
        list($match_codes,) = explode('||',$this->betcode);
        $match_codes = explode('|',$match_codes);
        foreach ($match_codes as $match_code){
            list($match_id,$codes) = explode(':',$match_code);
            $codes = explode(',',$codes);
            $match = Match::findById($match_id);

            foreach ($codes as $k => $code) {
                $in_time_arr[$match_id][$code] = $match->GetOddsByCode($code);
            }
        }
        $this->odds_in_time = json_encode($in_time_arr,true);
        return $this;
    }
    public function GetCodesByMatchId($m_id){
        list($match_codes,) = explode('||',$this->betcode);
        $match_codes = explode('|',$match_codes);
        foreach ($match_codes as $match_code){
            list($match_id,$codes) = explode(':',$match_code);
            if($m_id == $match_id){
                $codes = explode(',',$codes);
                return $codes;
            }
        }
        return null;
    }

    static function findById($id){
        if(!isset(self::$orders[$id])){
            self::$orders[$id] = static::findOne($id);
        }
        return  self::$orders[$id];
    }
}
