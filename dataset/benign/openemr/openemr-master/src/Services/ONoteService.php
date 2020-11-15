<?php

/**
 * ONoteService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\Connector;
use OpenEMR\Common\Logging\Logger;
use OpenEMR\Entities\ONote;
use OpenEMR\Services\UserService;

class ONoteService
{
    /**
     * Logger used primarily for logging events that are of interest to
     * developers.
     */
    private $logger;

    /**
     * The onote repository to be used for db CRUD operations.
     */
    private $repository;

    /**
     * Service used for correlating a user with a new onote.
     */
    private $userService;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->logger = new Logger("\OpenEMR\Services\ONoteService");
        $database = Connector::Instance();
        $entityManager = $database->entityManager;
        $this->repository = $entityManager->getRepository('\OpenEMR\Entities\ONote');
        $this->userService = new UserService();
    }

    /**
     * Creates a new office note.
     *
     * @param The text of the office note.
     * @return $body New id.
     */
    public function add($body)
    {
        $newNote = new ONote();
        $newNote->setBody($body);
        $newNote->setGroupName($this->userService->getCurrentlyLoggedInUserGroup());
        $newNote->setUser($this->userService->getCurrentlyLoggedInUser());
        $newNote->setActivity(1);
        $newNote->setDate(new \DateTime());

        $this->logger->debug("Adding new office note");
        $result = $this->repository->save($newNote);

        if (empty($result)) {
            $this->logger->error("Failed adding new office note");
        }

        $this->logger->debug("Added new office note " . $result);

        return $result;
    }

    /**
     * Toggles a office note to be enabled.
     *
     * @param $id The office note id.
     * @return true/false if the update was successful.
     */
    public function enableNoteById($id)
    {
        $this->logger->debug("Enabling office note with id " . $id);
        $result = $this->repository->enableNoteById($id);

        if (empty($result)) {
            $this->logger->error("Failed updating office note " . $id);
        }

        return $result;
    }

    /**
     * Toggles a office note to be disabled.
     *
     * @param $id The office note id.
     * @return true/false if the update was successful.
     */
    public function disableNoteById($id)
    {
        $this->logger->debug("Disabling office note with id " . $id);
        $result = $this->repository->disableNoteById($id);

        if (empty($result)) {
            $this->logger->error("Failed updating office note " . $id);
        }

        return $result;
    }

    /**
     * Get office notes with filters.
     *
     * @param $activity -1/0/1 to indicate filtered notes.
     * @param $offset The start index for pagination.
     * @param $limit The limit for pagination.
     * @return list of office notes.
     */
    public function getNotes($activity, $offset, $limit)
    {
        $this->logger->debug("Getting " . $activity . " onotes with filters: " . $offset . " " . $limit);
        return $this->repository->getNotes($activity, $offset, $limit);
    }
}
