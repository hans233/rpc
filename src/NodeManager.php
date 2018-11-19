<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/11/12
 * Time: 10:34 PM
 */

namespace EasySwoole\Rpc;


use Swoole\Table;

class NodeManager implements NodeManagerInterface
{
    private $swooleTable;

    function __construct(int $maxNodes = 4096)
    {
        $this->swooleTable = new \swoole_table($maxNodes);
        $this->swooleTable->column('nodeId',Table::TYPE_STRING,8);
        $this->swooleTable->column('serviceName',Table::TYPE_STRING,32);
        $this->swooleTable->column('serviceVersion',Table::TYPE_STRING,8);
        $this->swooleTable->column('serviceIp',Table::TYPE_STRING,15);
        $this->swooleTable->column('servicePort',Table::TYPE_INT);
        $this->swooleTable->column('lastHeartBeat',Table::TYPE_INT);
        $this->swooleTable->column('nodeExpire',Table::TYPE_INT);
        $this->swooleTable->create();
    }

    function getServiceNodes(string $serviceName,?string $version = null):array
    {
        $ret = [];
        foreach ($this->swooleTable as $key => $item){
            if($item['serviceName'] == $serviceName){
                //检测过期
                if(abs(time() - $item['lastHeartBeat']) > $item['nodeExpire']){
                    continue;
                }
                if(($version !== null) && ($item['serviceVersion'] != $version)){
                    continue;
                }
                array_push($ret,new ServiceNode($item));
            }
        }
        return $ret;
    }

    function getServiceNode(string $serviceName, ?string $version = null): ?ServiceNode
    {
        // TODO: Implement getServiceNode() method.
//        $allNodes = $this->getServiceNodes($serviceName,$version);
//        if(!empty($allNodes)){
//            mt_srand();
//            $key = array_rand($allNodes);
//            return $allNodes[$key];
//        }else{
//            return null;
//        }
        return new ServiceNode([
            'serviceIp'=>'127.0.0.1',
            'servicePort'=>9601
        ]);
    }

    function refreshServiceNode(ServiceNode $serviceNode)
    {
        // TODO: Implement refreshServiceNode() method.
        $array = $serviceNode->toArray();
        $array['lastHeartBeat'] = time();
        $this->swooleTable->set($serviceNode->getNodeId(),$array);
    }

}