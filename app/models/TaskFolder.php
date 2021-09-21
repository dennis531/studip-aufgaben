<?php
/**
 *  HiddenFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Dominik Feldschnieders <dofeldsc@uos.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */

use EPP\Permissions;
use EPP\TaskUsers;

class TaskFolder extends StandardFolder
{

    public static $sorter = 7;

    /**
     * @inherit
     */
    public static function getTypeName()
    {
        return _("Aufgabenordner");
    }

    /**
     * @inherit
     */
    public static function availableInRange($range_id_or_object, $user_id)
    {
        $range_id = is_object($range_id_or_object) ? $range_id_or_object->id : $range_id_or_object;
        return Seminar_Perm::get()->have_studip_perm('tutor', $range_id, $user_id);
    }

    /**
     * @inherit
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = count($this->getSubfolders()) + count($this->getFiles()) === 0
               ? 'folder-lock-empty+visibility-invisible'
               : 'folder-lock-full+visibility-invisible';
        return Icon::create($shape, $role);
    }

    /**
     * @inherit
     */
    public function isFileDownloadable($file, $user_id)
    {
        global $perm;

        if ($perm->have_studip_perm('tutor', $this->range_id, $user_id)) {
            return true;
        }

        if ($file->user_id == $user_id) {
            return true;
        }

        if ($this->data_content['task_user'] == $user_id) {
            return true;
        }

        if ($this->data_content['task_id']) {
            // check, if user has been granted access to this task
            $task_user = TaskUsers::findOneBySQL('ep_tasks_id = ? AND user_id = ?', [
                $this->data_content['task_id'],
                $this->data_content['task_user']
            ]);

            if (!empty($task_user) &&
                !empty($task_user->perms->findOneBy('user_id', $user_id))
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inherit
     */
    public function isReadable($user_id)
    {
        global $perm;

        if ($perm->have_studip_perm('tutor', $this->range_id, $user_id)) {
            return true;
        }

        return false;
    }

    /**
     * @inherit
     */
    public function isVisible($user_id)
    {
        return $this->isReadable($user_id);
    }
}