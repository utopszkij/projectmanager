<?php

class MembersModel {

    /**
     * error Message
     * @var string
     */
    protected $errorMsg = '';

    function __construct() {
        $db = new DB();
        $db->statement('
          CREATE TABLE IF NOT EXISTS members (
                `project_id` varchar(128) NOT NULL DEFAULT "" COMMENT "project id ",
                `avatar` varchar(128) NOT NULL DEFAULT "" COMMENT "user avatar url",
                `admin` int(1) NOT NULL DEFAULT 0 COMMENT "1: admin, 0: not admin",
                `nick` varchar(128) NOT NULL DEFAULT "" COMMENT "nick name",
                KEY `idx_project` (`project_id`),
                KEY `idx_avatar` (`avatar`)
          )
        ');
    }
    
    /**
     * set lastUpdate time into database
     * @return integer latUpdateTime
     */
    protected function setLastUpdate(): int {
        $table = DB::table('lastupdate');
        $record = $table->first();
        if ($record) {
            $record->time = time();
            $table->update($record);
        } else {
            $record = new stdClass();
            $record->time = time();
            $table->insert($record);
        }
        return $record->time;
    }
        
    /**
     * save all members from project json string
     * @param string $projectId
     * @param string $project json string
     * @return void
     */
    public function saveAllMembersFromJson(string $projectId, string $project) {
        $pr = JSON_decode($project);
        $table = DB::table('members');
        $table->where(array('project_id','=',$projectId))->delete();
        if (isset($pr->members)) {
            foreach ($pr->members as $member) {
                $member->project_id = $projectId;
                $table->insert($member);
            }
        }
        $this->setLastUpdate();
    }
    
    /**
     * update one member record
     * @param Request $request
     * @param string $projectId
     * @param string $avatar
     * @param int $admin  0|1
     * @param string $nick
     * @return string {"errorMsg":"..."}
     */
    public function updateMemberAdmin(& $request, string $projectId,
        string $avatar, int $admin): string {
            if ($request->sessionGet('loggedUser') == '') {
                return '{"errorMsg":"'.txt('WRONGSESSION').'"}';
            }
            if (!in_array($request->sessionGet('loggedUser'), $request->sessionGet('admins'))) {
                return '{"errorMsg":"'.txt('ACCESSDENIED').'"}';
            }
            $result = '';
            $table = DB::table('members');
            $table->where(array('project_id','=',$projectId))
            ->where(array('avatar','=',$avatar));
            $member = $table->first();
            if (($member) && ($member->admin != $admin)) {
                $member->admin = $admin;
                $table->update($member);
                $result = $table->getErrorMsg();
                $this->setLastUpdate();
            }
            return '{"errorMsg":"'.$result.'"}';
    }
    
    /**
     * delete invalid members from database by members array
     * @param string $projectId
     * @param array $members array of Member
     * @return string mysql errorMsg or ''
     */
    protected function deleteInvalidMembers(string $projectId, array $members): string {
        $result = '';
        $table = DB::table('members');
        $table->where(array('project_id','=',$projectId));
        $oldMembers = $table->get();
        foreach ($oldMembers as $oldMember) {
            $exists = false;
            foreach ($members as $member) {
                if ($member->avatar == $oldMember->avatar) {
                    $exists = true;
                }
            }
            if (!$exists) {
                $table = DB::table('members');
                $table->where(array('project_id','=',$projectId))
                ->where(array('avatar','=',$oldMember->avatar));
                $table->delete();
                if ($table->getErrorMsg() != '') {
                    $result = $table->getErrorMsg();
                }
            }
        }
        return $result;
    }
    
    /**
     * save all member from array
     * @param string $projectId
     * @param array $members [{"avatar":"..", "nick":"..", "admin":0|1}, ...]
     * @return string
     */
    public function saveAllMembers(string $projectId, array $members): string {
        $result = '';
        
        // insert or update members
        foreach ($members as $member) {
            $member->project_id = $projectId;
            $table = DB::table('members');
            $table->where(array('project_id','=',$projectId))
            ->where(array('avatar','=',$member->avatar));
            $oldMember = $table->first();
            if ($oldMember) {
                if (($oldMember->admin != $member->admin) | ($oldMember->nick != $member->nick)) {
                    $table->update($member);
                }
            } else {
                $table->insert($member);
            }
            if ($table->getErrorMsg() != '') {
                $result = $table->getErrorMsg();
            }
        }
        
        // check oldMembers exists in members? if not exists delete it
        $result .= $this->deleteInvalidMembers($projectId, $members);
        
        $this->setLastUpdate();
        return '{"errorMsg":"'.$result.'"}';
    }
} // class
?>