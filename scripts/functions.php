<?php


function isEmpty($user,$email,$password,$passwordAgain,$type)
{
	if(empty($user) || empty($email) || empty($password) || empty($passwordAgain) || empty($type))
	{
		return true;
	}
	else
		return false;
}

function isEmptyLogin($user,$password)
{
	if(empty($user) || empty($password))
	{
		return true;
	}
	else
		return false;
}

function invalidUsername($user)
{
	//check if username is valid 
	
	if(strlen($user)<4 || strlen($user)>128)//works
	{
		return true;
	}
	elseif(!preg_match("/^[a-zA-z0-9]*$/",$user)) //not sure if + would work better than *
	{
		return true;
	}
}

function usernameExists($con,$user)
{
	//check if username already exists in database
	//Allow duplicate emails for multiple account because the account types are final
	
	$sql = "SELECT * FROM users WHERE usersName = ?;";
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ../register.php?error=stmtFailed");
	}
		
	mysqli_stmt_bind_param($stmt, "s", $user); 
	//ss stands for 2 strings this replaces ? placeholder with the actual username in the sql statement
	mysqli_stmt_execute($stmt);
	
/*
	$sqll = "SELECT * FROM users WHERE usersName = '$user';";
	$result=mysqli_query($con,$sqll);
	
	$rows = mysqli_num_rows($result);
*/	
	$result = mysqli_stmt_get_result($stmt);
	$row = mysqli_fetch_assoc($result);
	//echo($row['usersName']);
		
	mysqli_stmt_close($stmt);
	if($row)//not working
	{
		if($row['usersName']===$user)
			return $row;
	}
	else
	{
		return false;
	}
	
}

function invalidEmail($email)
{
	if(!filter_var($email,FILTER_VALIDATE_EMAIL))
	{
		return true;
	}
	else
		return false;
}

function invalidPassword($password,$passwordAgain)
{
	if($password != $passwordAgain)
	{
		return true;
	}
	else if(strlen($password)<8 || strlen($password)>128)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function createUser($con,$user,$email,$password,$type)
{
	$sql = "INSERT INTO users(usersName, usersEmail, usersType, usersPassword ) VALUES(?, ?, ?, ?);";
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ../register.php?error=stmtFailed");
	}
	
	$hashedPassword = password_hash($password,PASSWORD_DEFAULT);
		
	mysqli_stmt_bind_param($stmt, "ssss", $user,$email,$type,$hashedPassword); 
	mysqli_stmt_execute($stmt);
		
	mysqli_stmt_close($stmt);
	
	header("location: ../register.php?error=registerSuccess");
	
	exit();	
}

function loginUser($con,$user,$password)
{
	$userExists = usernameExists($con,$user); //if user exists fetch the table row with it
	
	if($userExists===false)
	{
		header("location: ..?error=invalidUser");
		exit();
	}
	
	$hashedPassword = $userExists["usersPassword"];
	$passwordCheck = password_verify($password,$hashedPassword);
	
	if($passwordCheck === false)
	{
		header("location: ..?error=invalidPassword");
		/*
		echo $hashedPassword;
		echo '\n';
		echo password_hash($password,PASSWORD_DEFAULT);
		*/
		
	}
	else if($passwordCheck === true)
	{
		session_id($userExists["usersId"]);
		session_start();
		$_SESSION["usersId"] = $userExists["usersId"];
		$_SESSION["usersName"] = $userExists["usersName"];
		$_SESSION["usersType"] = $userExists["usersType"];
		
		header("location: ..?error=loginSuccess");
		
	}
}

function callJavascript($functionName)
{
	echo '<script type="text/javascript">',
			$functionName.'();',
		  '</script>';
}

function insertInProjects($con,$projectName,$user)
{
	$sql = "INSERT INTO projects(projectCode, projectName, projectOwner) VALUES(?, ?, ?);";
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ../projects.php?error=stmtFailed");
		exit();
	}
	
	$code = uniqid();
	
	mysqli_stmt_bind_param($stmt, "sss", $code,$projectName,$user); 
	mysqli_stmt_execute($stmt);
		
	mysqli_stmt_close($stmt);
	
	$con->close();	
	header("location: ../projects.php?error=projectCreated");
}

function isDbEmpty($con,$dbName)
{
	 if ($result = mysqli_query($con,"SHOW TABLES FROM ".$dbName.";")) {
        if (mysqli_num_rows($result)== 0)
		{
			return true;
		}
		else
		{
			return false;
		}
    }

}

function createDefualtTables($con)
{
	$sql = "CREATE TABLE users
	(
		usersId int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
		usersName VARCHAR(128) NOT NULL,
		usersEmail VARCHAR(128) NOT NULL,
		usersType VARCHAR(32) NOT NULL,
		usersPassword VARCHAR(128) NOT NULL
	);";
	$sql .= "CREATE TABLE projects
	(
		projectCode VARCHAR(128) NOT NULL,
		projectName VARCHAR(128) NOT NULL,
		projectOwner VARCHAR(128) NOT NULL
	);";
	$sql .= "CREATE TABLE developers
	(
		developersName VARCHAR(128) NOT NULL,
		developersProject VARCHAR(128) NOT NULL
	);";
	$sql .="CREATE TABLE issues
	(
		issueId int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
		issuePlace VARCHAR(128) NOT NULL,
		issueTitle VARCHAR(128) NOT NULL,
		issueDetails TEXT NOT NULL,
		issuePriority int(2) NOT NULL,
		issueStatus VARCHAR(128) NOT NULL,
		issueCreatedBy VARCHAR(128) NOT NULL,
		issueDevelopedBy VARCHAR(128) NOT NULL,
		issueProject VARCHAR(128) NOT NULL
	);";
	
	if ($con->multi_query($sql) === TRUE)
	{
		header("location: ../index.php?error=tableCreationSuccess");
	} 
	else {
		header("location: ../index.php?error=tableCreationFailed");
		exit();
	}
	
	
}

function listEntries($con, $sql, $placeholderValue)
{
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		//echo "aa";
		header("location: ../projects.php?error=stmtFailed");
	}
	
		
	mysqli_stmt_bind_param($stmt, "s", $placeholderValue); 
	
	mysqli_stmt_execute($stmt);
	
	$result = mysqli_stmt_get_result($stmt);
		
	mysqli_stmt_close($stmt);
	
	return $result;
}

function listProjectsForManager($con, $user)
{
	$sql = 'SELECT projectCode,projectName FROM projects WHERE projectOwner=?;';
	$result = listEntries($con,$sql,$user);
	return $result;
}

function listProjectsForDeveloper($con, $user)
{
	$sql = 'SELECT developersProject projectCode,projectName FROM developers,projects WHERE developers.developersProject=projectCode AND developersName= ? ;';
	$result = listEntries($con,$sql,$user);
	return $result;
}

function listIssue($con, $issueId)
{
	$sql = 'SELECT * FROM issues WHERE issueId=?;';
	$result = listEntries($con,$sql,$issueId);
	return $result;
}

function entryExists($con,$sql,$placeholder1,$placeholder2)
{
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ./projects.php?error=stmtFailed");
	}
	
		
	mysqli_stmt_bind_param($stmt, "ss", $placeholder1 ,$placeholder2); 
	
	mysqli_stmt_execute($stmt);
	
	$result = mysqli_stmt_get_result($stmt);
	
	if(mysqli_num_rows($result)!= 0)
	{
		mysqli_stmt_close($stmt);
		return true;
	}
	else
	{
		mysqli_stmt_close($stmt);
		return false;
	}
}

function isDevInProject($con,$code,$user)
{
	$sql = "SELECT developersName, developersProject FROM developers WHERE developersProject = ? AND developersName = ?";
	$result = entryExists($con,$sql,$code,$user);
	return $result;
	
}

function isOwnerProject($con,$code,$user)
{
	$sql = "SELECT projectOwner, projectCode FROM projects WHERE projectCode = ? AND projectOwner = ?";
	$result = entryExists($con,$sql,$code,$user);
	return $result;	
}

function issueBelongsToProject($con, $issueId, $projectCode)
{
	$sql = "SELECT issueId, issueProject FROM issues WHERE issueId = ? AND issueProject = ?";
	$result = entryExists($con,$sql,$issueId,$projectCode);
	return $result;		
}

function issueInPlace($con, $issueId, $place)
{
	$sql = "SELECT issueId, issuePlace FROM issues WHERE issueId = ? AND issuePlace = ?";
	$result = entryExists($con,$sql,$issueId,$place);
	return $result;
}

function isIssueCreatedBy($con,$issueId,$user)
{
	$sql = "SELECT issueId, issueCreatedBy FROM issues WHERE issueId = ? AND issueCreatedBy = ?";
	$result = entryExists($con,$sql,$issueId,$user);
	return $result;
}

function isIssueDevelopedBy($con, $issueId, $user)
{
	$sql = "SELECT issueId, issueCreatedBy FROM issues WHERE issueId = ? AND issueDevelopedBy = ?";
	$result = entryExists($con,$sql,$issueId,$user);
	return $result;
}

function existsIssueInPlace($con, $place, $projectCode)
{
	$sql = "SELECT issueId FROM issues WHERE issuePlace = ? AND issueProject = ?;";
	$result = entryExists($con,$sql,$place,$projectCode);
	return $result;
}

function projectExists($con,$code)
{
	$sql = "SELECT projectCode,projectName FROM projects WHERE projectCode=?";
	
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ./projects.php?error=stmtFailed");
	}
	
		
	mysqli_stmt_bind_param($stmt, "s", $code); 
	
	mysqli_stmt_execute($stmt);
	
	$result = mysqli_stmt_get_result($stmt);
	
	if(mysqli_num_rows($result)!= 0)
	{
		mysqli_stmt_close($stmt);
		return true;
	}
	else
	{
		mysqli_stmt_close($stmt);
		return false;
	}
}

function addDevToProject($con,$code,$user)
{
	$sql = "INSERT INTO developers(developersName, developersProject) VALUES(?, ?);";
	
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ./projects.php?error=stmtFailed");
	}
	
		
	mysqli_stmt_bind_param($stmt, "ss", $user, $code); 
	
	mysqli_stmt_execute($stmt);
	
	$result = mysqli_stmt_get_result($stmt);
	
	mysqli_stmt_close($stmt);
}

function shortenDisplay($string,$maxSize)
{
	if(strlen($string)>$maxSize)
	{
		$result = substr($string,0,$maxSize);
		$result .="...";
		return $result;
	}
	else
	{
		return $string;
	}
}

function createIssue($con,$title,$details,$priority,$user,$projectCode)
{
	$sql = "INSERT INTO issues(issuePlace, issueTitle, issueDetails, issuePriority, issueStatus,
			issueCreatedBy, issueDevelopedBy, issueProject ) VALUES(?, ?, ?, ?, ?, ?, ?, ?);";
	
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ./projects.php?error=stmtFailed");
	}
	$defaultPlace = "Backlog";
	$defaultStatus = "Waiting";
	$defaultDeveloper = "None";
		
	mysqli_stmt_bind_param($stmt, "ssssssss", $defaultPlace, $title, $details, $priority, $defaultStatus, $user, $defaultDeveloper,$projectCode); 
	
	mysqli_stmt_execute($stmt);
	
	$result = mysqli_stmt_get_result($stmt);
	
	mysqli_stmt_close($stmt);
	
}

function listIssuesForProject($con, $place, $projectCode)
{
	$sql = 'SELECT issueId,issuePriority,issueTitle FROM issues WHERE issuePlace=? AND issueProject=? ORDER BY issuePriority desc;';
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ./projects.php?error=stmtFailed");
	}
	
		
	mysqli_stmt_bind_param($stmt, "ss", $place,$projectCode); 
	
	mysqli_stmt_execute($stmt);
	
	$result = mysqli_stmt_get_result($stmt);
		
	mysqli_stmt_close($stmt);
	
	return $result;
	
}

function updateEntry($con, $sql, $value, $id)
{
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ./projects.php?error=stmtFailed");
	}
		
	mysqli_stmt_bind_param($stmt, "ss", $value, $id); 
	
	mysqli_stmt_execute($stmt);
		
	mysqli_stmt_close($stmt);
}

function moveFromBacklog($con,$issueId)
{
	$sql = "UPDATE issues
			SET issuePlace = ?
			WHERE issueId = ?;";
	updateEntry($con,$sql,"To Do",$issueId);
	
}

function postponeFromToDo($con,$issueId)
{
	$sql = "UPDATE issues
			SET issuePlace = ?
			WHERE issueId = ?;";
	updateEntry($con,$sql,"Backlog",$issueId);
}

function editIssue($con,$newTitle,$newPriority,$newDetails,$issueId)
{
	$sql = "UPDATE issues
			SET issueTitle = ?, issuePriority = ?, issueDetails = ?
			WHERE issueId = ?;";
			
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ./projects.php?error=stmtFailed");
	}
		
	mysqli_stmt_bind_param($stmt, "ssss", $newTitle,$newPriority,$newDetails,$issueId); 
	
	mysqli_stmt_execute($stmt);
		
	mysqli_stmt_close($stmt);
}

function updateIssueExecute($con,$sql, $newPlace, $newStatus, $developer, $issueId)
{
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ./projects.php?error=stmtFailed");
	}
		
	mysqli_stmt_bind_param($stmt, "ssss", $newPlace,$newStatus,$developer, $issueId); 
	
	mysqli_stmt_execute($stmt);
		
	mysqli_stmt_close($stmt);
}

function updateIssue($con, $newPlace, $newStatus, $developer, $issueId)
{
	$sql = "UPDATE issues
			SET issuePlace = ?, issueStatus = ?, issueDevelopedBy = ? 
			WHERE issueId = ?;";
			
	updateIssueExecute($con,$sql, $newPlace, $newStatus, $developer, $issueId);
	
}
function abandonIssue($con, $newPlace, $newStatus, $developer, $issueId)
{
	$sql = "UPDATE issues
			SET issuePlace = ?, issueStatus = ?, issuePriority = 0, issueDevelopedBy = ? 
			WHERE issueId = ?;";
			
	updateIssueExecute($con,$sql, $newPlace, $newStatus, $developer, $issueId);
}

function getIssue($con,$issueId)
{
	$issue = listIssue($con,$issueId);
	$row = mysqli_fetch_array($issue);
	return $row;
}
function deleteEntry($con, $sql, $id)
{
	$stmt = mysqli_stmt_init($con);
	if(!mysqli_stmt_prepare($stmt,$sql))
	{
		header("location: ./projects.php?error=stmtFailed");
	}
		
	mysqli_stmt_bind_param($stmt, "s", $id); 
	
	mysqli_stmt_execute($stmt);
		
	mysqli_stmt_close($stmt);
}


function deleteIssue($con, $issueId)
{
	$sql = "DELETE FROM issues WHERE issueId = ?;";
	deleteEntry($con,$sql,$issueId);
}
function deleteIssues($con, $projectCode)
{
	$sql = "DELETE FROM issues WHERE issueProject = ?;";
	deleteEntry($con,$sql,$projectCode);
}
function deleteProjectFromDev($con, $projectCode)
{
	$sql = "DELETE FROM developers WHERE developersProject = ?;";
	deleteEntry($con,$sql,$projectCode);
}

function deleteProject($con,$projectCode)
{
	deleteIssues($con, $projectCode);
	deleteProjectFromDev($con, $projectCode);
	
	$sql = "DELETE FROM projects WHERE projectCode = ?;";
	deleteEntry($con,$sql,$projectCode);
}



