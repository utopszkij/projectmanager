<?php
class MembersController {

    protected function setHtmlHeader() {
        if (!headers_sent()) {
            header('Content-Type: json');
        }
    }
    
    /**
     * update one member AJAX backend server
     * @param Request $request
     * - string projectid
     * - string avatar
     * - string admin
     * - string sid
     * @return void
     */
    public function memberupdate(Request $request) {
        $this->setHtmlHeader();
        $projectId = $request->input('projectid','0');
        $avatar = $request->input('avatar','0');
        $admin = $request->input('admin','1');
        
        // update in database
        $model = getModel('members');
        echo $model->updateMemberAdmin($request, $projectId, $avatar, $admin);
    }
    
    /**
     * save all members AJAX backend server
     * @param Request $request
     * - string projectid
     * - string members json string [{...}, ...]
     * - string sid
     * @return void   echo  {"errorMsg":""}
     */
    public function saveallmembers(Request $request) {
        $this->setHtmlHeader();
        $projectId = $request->input('projectid','0');
        $members = JSON_decode($request->input('members','[]'));
        $model = getModel('members');
        echo $model->saveAllMembers($projectId, $members);
    }
}
?>