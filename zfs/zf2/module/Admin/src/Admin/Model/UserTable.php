<?php

namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;

class UserTable extends AbstractTableGateway {

    protected $table = 'tbl_user';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    /*public function fetchAll() {
        $resultSet = $this->select(function (Select $select) {
                    #$select->order('created ASC');
                });
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new Entity\User();
            $entity->exchangeArray($row);
            $entities[] = $entity;
        }
        return $entities;
    }*/
    public function getUser($id) {
        $row = $this->select(array('id' => (int) $id))->current();
        if (!$row)
            return false;
        //$entity = new Entity\User();
        //return $entity->exchangeArray((array)$row);
        return $row;
    }
    // public function getRegisteredUser($id,$act_code) {
 //        $row = $this->select(array('id' => (int) $id,'activation_key'=>$act_code,'IsUsed'=>0))->current();
 //        if (!$row)
 //            return false;
 //        $entity = new Entity\User();
 //        return $entity->exchangeArray((array)$row);
 //    }
    //  public function activateUser($id) {
    //   $data=array('IsUsed'=>1,'IsActive'=>1);
    //  $check= $this->update($data, array('id' => $id));        
    //  if(!$check)
    //      return false;
    //  return $check;
 //    }

 //    public function saveUser($address,$act_code) {
 //        //$data = $address->getArrayCopy();
    //  $data = $address;
 //        $id = (int) $address['id'];
    //  $data['ip'] = $_SERVER["REMOTE_ADDR"];
    //  $year=date('y');
    //  $mon=date('m');
 //        if ($id == 0) {
    //      $data['created_date'] = date("Y-m-d H:i:s");
    //      $data['password'] = md5($data['password']);
    //       $random_id_length=6;
    //      $rnd_id = crypt(uniqid(rand(),1)); 
    //      //to remove any slashes that might have come 
    //      $rnd_id = strip_tags(stripslashes($rnd_id)); 
    //      //Removing any . or / and reversing the string 
    //      $rnd_id = str_replace(".","",$rnd_id); 
    //      $rnd_id = strrev(str_replace("/","",$rnd_id)); 
    //      $ref_no= "RS".$year.$mon.substr($rnd_id,0,$random_id_length);
    //      $data['ref_no'] = $ref_no; 
    //      $data['activation_key'] = $act_code;
 //            if (!$this->insert($data))
 //                return false;
 //            return $this->getLastInsertValue();
 //        }
 //        elseif ($this->getUser($id)) {
 //            if (!$this->update($data, array('id' => $id)))
 //                return false;
 //            return $id;
 //        }
 //        else
 //            return false;
 //    }

 //    public function removeUser($id) {
 //        return $this->delete(array('id' => (int) $id));
 //    }
 //    public function selectList($columns=array('*')){
 //        $country= $this->select(function (Select $select) use ($columns) {
 //            $select->columns($columns);
    //      $select->where(array('active'=>1));
 //        })->toArray();
 //        foreach($country as $c)$list[$c[$columns[0]]]=$c[$columns[1]];
 //        return $list;
 //    }

    // public function searchuser($cols='')
    // {
    //     $result = $this->select(function(Select $select) use ($cols){
    //         $select->where()->like("email","m%"); 
    //     });
    //     return $result;
    // }

}