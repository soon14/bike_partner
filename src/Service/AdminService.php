<?php

namespace Bike\Partner\Service;

use Bike\Partner\Exception\Debug\DebugException;
use Bike\Partner\Exception\Logic\LogicException;
use Bike\Partner\Service\AbstractService;
use Bike\Partner\Util\ArgUtil;

class AdminService extends AbstractService
{
    public function createAdmin(array $data)
    {
        $data = ArgUtil::getArgs($data, array(
            'name'
        ));
        $this->validateName($data['name']);
        $adminDao = $this->getAdminDao();
        $adminConn = $adminDao->getConn();
        $passportService = $this->container->get('bike.partner.service.passport');
        $passportDao = $this->container->get('bike.partner.dao.partner.passport');
        $passportConn = $passportDao->getConn();
        $adminConn->beginTransaction();
        $passportConn->beginTransaction();
        try {
            $adminDao->create($data);
            $passportService->createPassport($data);
            $adminConn->commit();
            $passportConn->commit();
        } catch (\Exception $e) {
            $adminConn->rollBack();
            $passportConn->rollBack();
            throw $e;
        }
    }

    protected function validateName($name)
    {
        if (!$name) {
            throw new LogicException('管理员名称不能为空');
        }
        $len = mb_strlen($name);
        if ($len > 20) {
            throw new LogicException('管理员名称不能多于20个字符');
        }
    }

    protected function getAdminDao()
    {
        return $this->container->get('bike.partner.dao.partner.admin');
    }
}
 