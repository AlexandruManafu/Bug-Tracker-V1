<?php
if(isset($_POST["submit"]))
{
	session_start();
	$title = $_POST["issueTitle"];
	$details = $_POST["issueDetails"];
	$priority = $_POST["issuePriority"];
	$issueId = $_POST["issueId"];
	$projectCode = $_POST["projectCode"];
	
	
	if(empty($title) || empty($details) || empty($priority))
	{
		header("location: ../project.php?project=".$projectCode."&error=emptyInput");
		exit();
	}
	
	require_once 'database-handler.php';
	require_once 'functions.php';
	
	editIssue($con,$title,$priority,$details,$issueId);
	header("location: ../project.php?project=".$projectCode."&selectedIssue=".$issueId);
	exit();
	
	
}
else //redirect user back if he tries to access this page by inputing it on the bar
{
	header("location: ../projects.php");
	exit();
}

