<?php
class tasksModel {

	/**
	* refresh tasks from database
	* @param projectId string  REQUED
	* @param fileTime number   REQUED
	* @return jsonStr 
	*      {"fileTime".num} vagy {"fileTime".num, "project":XMLstr}  
	*/
	public function refresh($projectId, $fileTime) {
		$result = new stdClass();
		$result->fileTime = 0;
		if (file_exists('./projects/project_'.$projectId.'.xml')) {
					$actFileTime = filemtime('./projects/project_'.$projectId.'.xml');
				   if ($actFileTime > $fileTime) {
						$lines = file('./projects/project_'.$projectId.'.xml');
						$result->project = JSON_encode(implode("",$lines));   
					}
					$result->fileTime = $actFileTime;
	   }
		return JSON_encode($result);
	}
	
	/**
	* save tasks into database 
	* @param projectId string  REQUED
	* @param project XMLstr    REQUED
	* @return jsonStr 
	*    {"fileTime".num}  
	*/
	public function save($projectId, $project) {
		$fp = fopen('./projects/project_'.$projectId.'.xml','w+');
		fwrite($fp, $project);
		fclose($fp);
		$fileTime = filemtime('./projects/project_'.$projectId.'.xml');
	   $result = new stdClass();
	   $result->fileTime = $fileTime;
		return JSON_encode($result);
	} 
}
?>