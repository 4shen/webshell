<?php

/**
 * AppointmentRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\AppointmentService;
use OpenEMR\RestControllers\RestControllerHelper;

class AppointmentRestController
{
    private $appointmentService;

    public function __construct()
    {
        $this->appointmentService = new AppointmentService();
    }

    public function getOne($eid)
    {
        $serviceResult = $this->appointmentService->getAppointment($eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAll()
    {
        $serviceResult = $this->appointmentService->getAppointmentsForPatient(null);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAllForPatient($pid)
    {
        $serviceResult = $this->appointmentService->getAppointmentsForPatient($pid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function post($pid, $data)
    {
        $validationResult = $this->appointmentService->validate($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->appointmentService->insert($pid, $data);
        return RestControllerHelper::responseHandler(array("id" => $serviceResult), null, 200);
    }

    public function delete($eid)
    {
        $serviceResult = $this->appointmentService->delete($eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }
}
