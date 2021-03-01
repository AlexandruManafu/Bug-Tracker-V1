<?php
include('header.php');
?>
	
	<div class="wrapper">
	<div class="content">
	<?php
		require_once "scripts/functions.php";
		require_once "scripts/database-handler.php";
		if(isset($_SESSION["usersType"]) && $_SESSION["usersType"]=="manager")
		{
			if(isset($_GET["project"]) && isOwnerProject($con,$_GET["project"],$_SESSION["usersName"]) )
			{
				$code = $_GET["project"];
				//echo "<p>".$code."</p>";
			}
			else
			{
				header("location: ./projects.php?error=projectNotOwned");
				exit();
			}
			
		}
		else if(isset($_SESSION["usersType"]) && $_SESSION["usersType"]=="developer")
		{
			if(isset($_GET["project"]) && isDevInProject($con,$_GET["project"],$_SESSION["usersName"]) )
			{
				$code = $_GET["project"];
				//echo "<p>".$code."</p>";
			}
			else
			{
				header("location: ./projects.php?error=projectNotJoined");
				exit();
			}
		}
		else
		{
			header("location: ./index.php");
		}
		
		require_once 'scripts/database-handler.php';
		require_once 'scripts/functions.php';
		
		function displayIssue($issue,$projectCode,$size,$maxTextLen)
		{
			if($issue["issuePriority"]==1)
			{
				$color = "green";
			}
			else if($issue["issuePriority"]==2)
			{
				$color = "orange";
			}
			else if($issue["issuePriority"]==3)
			{
				$color = "red";
			}
			else
			{
				$color = "grey";
			}
				
			echo "<a class='issueButton' href='project.php"."?project=".$projectCode."&selectedIssue=".$issue["issueId"].
						
				"'>
				<img class='issuePriority' src='images/icons/circle-".$color.".png' alt='Issue Priority' width =".$size."%> 
				<p>".shortenDisplay($issue['issueTitle'],$maxTextLen)."</p>
				</a>";
				
		}
		
		function displayIssues($con, $place,$projectCode)
		{
			$issues = listIssuesForProject($con, $place, $projectCode);
			
			while($row = mysqli_fetch_array($issues))
			{
				displayIssue($row,$projectCode,10,15);
				
			}
		}
		
		function displayConfirmationWindow($elementId,$formAction,$issue,$projectCode,$message,$nameHidden,$valueHidden)
		{
			echo "<div class='confirm' id=".$elementId." style='display:none;margin-left: 2ex;'>";
				echo "<form action = ".$formAction." method='post'>";
					if($issue!=NULL)
					{
						echo "<input type='hidden' name='issueId' value=".$issue["issueId"].">";
					}
					echo "<input type='hidden' name='projectCode' value=".$projectCode.">";
					if($nameHidden!=null)
					{
						echo "<input type='hidden' name=".$nameHidden." value=".$valueHidden.">";
					}
					echo "<p>".$message."</p>";
					echo "<button class='infoButton' type='text' name='yes'>Yes</button>";
					echo "<button class='infoButton' type='text' name='no'>No</button>";
				echo "</form>";
					
			echo "</div>";
		}
		
		function displayColumn($con,$columnName,$projectCode)
		{
			echo "<div class='column'><h2 class='columnTitle'>".$columnName."</h2>";
			echo "<br>";
		
			displayIssues($con,$columnName,$projectCode);

			echo "</div>";
		}
		function displayButton($functionsToCall,$displayText)
		{
			echo "<button class='infoButton' onclick=".$functionsToCall." type='text' name='option'>".$displayText."</button>";
		}
		function displaySpecialButton($class,$functionsToCall,$displayText)
		{
			echo "<button class=".$class." onclick=".$functionsToCall." type='text' name='option'>".$displayText."</button>";
		}
	
	echo "<div class='row'>";
	
		displayColumn($con,"Backlog",$code);
		
		displayColumn($con,"To Do",$code);
	
		displayColumn($con,"In Progress",$code);
		
		displayColumn($con,"Testing",$code);
		
		displayColumn($con,"Completed",$code);
		
	echo "</div>"; 
	
		echo "<div class='row'>";
		echo "<div class='leftCol'>";
			echo "<img class='addIssue' onclick=toggleWindow('newIssueWindow','inline-block');changeFlexValue('rightCol','newIssueWindow',3.5,2);changeMarginValue('deleteProject','newIssueWindow',3.7,25); src='images/icons/add.svg' alt='Create Project' width = 10%>";
			
			
			echo "<div class='confirm' id='newIssueWindow' style='display: none;'>";
			echo "<form action = 'scripts/createIssue-script.php' method='post'>";
				echo "<input type='hidden' name='projectCode' value=".$code.">";
				echo "<input class='bigger-custom-input' type='text' style='margin-left:-0.5ex;' name='issueTitle' placeholder = 'Issue Title'><br>";
				echo "<p style='margin-bottom: 1ex;'>Issue Priority: </p>";
				echo "<select name='issuePriority' id='account_type'>";
						echo "<option value=''>Select an option</option>";
						echo "<option value='1'>Low</option>";
						echo "<option value='2'>Medium</option>";
						echo "<option value='3'>High</option>";
						

				echo "</select>";
				echo "<br><br>";
				echo "<textarea class='details' name='issueDetails' rows='5'  placeholder = 'Details'></textarea><br>";
				
				
				echo "<button class='create_button' type='text' name='submit'>Create</button>";
				
				
				echo "</form>";
			echo "</div>";
			if(isset($_GET["error"]) && $_GET["error"]=="emptyInput" )
			{	
				echo "<p class=error style='margin-top:3ex;' = error>No inputs can be empty</p>";
			}
			if($_SESSION["usersType"] == "manager")
			{
				displaySpecialButton("deleteProject","exclusiveToggleWindow('confirm','deleteProjectWindow','block');","Delete Project");
				displayButton("exclusiveToggleWindow('confirm','displayCode','block');","Display Project Join Code");
			}
			echo "<div class=confirm id=displayCode style='display:none;'>
					<p>The join code for the current project is:</p><br>
					<p>".$code."</p>
				</div>
			";
			displayConfirmationWindow("deleteProjectWindow","scripts/updateIssue-script.php",NULL,$code,
			"Are you sure you want to delete the project with all its issues ?","targetPlace","Delete_Project");
			if(isset($_GET["error"]) && $_GET["error"]=="existActiveIssues" )
				{	
					echo "<p class = error>Projects with active issues cannot be deleted.</p>";
				}
			echo "</div>";
			
		
		echo "<div id='rightCol'>";
		//$backlog="Backlog";
		if(!isset($_GET["selectedIssue"]))
		{
			echo "<p class='noneSelected'>No issue selected</p>";
		}
		else if(isset($_GET["selectedIssue"]) && issueBelongsToProject($con, $_GET["selectedIssue"], $code))
		{
			//$issue = getIssue($con,$_GET["selectedIssue"]);
			echo "<p class='issueSelected'>Selected Issue:</p>";
			
			$issue = getIssue($con,$_GET["selectedIssue"]);
			
			displayIssue($issue,$code,4,50);
			
			
			displayButton("exclusiveToggleWindow('confirm','infoWindow','block');","View Issue Info");
			
			if($_SESSION["usersType"] == "manager" && !issueInPlace($con,$_GET["selectedIssue"],"Completed"))
			{
				displayButton("exclusiveToggleWindow('confirm','editWindow','block');","Edit Issue");
			}
			else if($_SESSION["usersType"] == "developer" &&
					((isIssueDevelopedBy($con, $_GET["selectedIssue"],$_SESSION["usersName"]) || isIssueCreatedBy($con,$_GET["selectedIssue"],$_SESSION["usersName"])) 
					&& !issueInPlace($con,$_GET["selectedIssue"],"Completed")))
			{
				displayButton("exclusiveToggleWindow('confirm','editWindow','block');","Edit Issue");
			}
			
			if(issueInPlace($con,$_GET["selectedIssue"],"Backlog") && $_SESSION["usersType"] == "manager")
			{
				displayButton("exclusiveToggleWindow('confirm','issueDelete','block');","Delete Issue");
				displayButton("exclusiveToggleWindow('confirm','issueMove','block');","Move Issue");
			}
			else if(issueInPlace($con,$_GET["selectedIssue"],"Backlog") && $_SESSION["usersType"] == "developer" && isIssueCreatedBy($con,$_GET["selectedIssue"],$_SESSION["usersName"]) )
			{
				displayButton("exclusiveToggleWindow('confirm','issueDelete','block');","Delete Issue");
			}
			
			
			if(issueInPlace($con,$_GET["selectedIssue"],"To Do") && $_SESSION["usersType"] == "manager")
			{
				displayButton("exclusiveToggleWindow('confirm','issuePostpone','block');", "Postpone Issue");
			}			
			else if(issueInPlace($con,$_GET["selectedIssue"],"To Do") && $_SESSION["usersType"] == "developer")
			{
				displayButton("exclusiveToggleWindow('confirm','issueUpdate','block');", "Update Status");
			}
			
			
			if(issueInPlace($con,$_GET["selectedIssue"],"In Progress") && $_SESSION["usersType"] == "developer")
			{
				displayButton("exclusiveToggleWindow('confirm','issueTesting','block');", "Update Status");
			}
			else if(issueInPlace($con,$_GET["selectedIssue"],"In Progress") && $_SESSION["usersType"] == "manager")
			{
				displayButton("exclusiveToggleWindow('confirm','issueAbandon','block');", "Abandon");
				displayButton("exclusiveToggleWindow('confirm','issueComplete','block');", "Mark as Completed");
			}
			
			if(issueInPlace($con,$_GET["selectedIssue"],"Testing") && $_SESSION["usersType"] == "manager")
			{
				displayButton("exclusiveToggleWindow('confirm','issueAbandon','block');", "Abandon");
				displayButton("exclusiveToggleWindow('confirm','issueComplete','block');", "Mark as Completed");
			}
				
			displayConfirmationWindow("issueDelete","scripts/updateIssue-script.php",$issue,$code,
			"Are you sure you want to delete the selected issue?","targetPlace","Delete");
			
			displayConfirmationWindow("issueMove","scripts/updateIssue-script.php",$issue,$code,
			"Are you sure you want to move the selected issue to 'To Do' ?","targetPlace","To_Do");
			
			displayConfirmationWindow("issuePostpone","scripts/updateIssue-script.php",$issue,$code,
			"Are you sure you want to move the selected issue to 'Backlog' ?","targetPlace","Backlog");
			
			displayConfirmationWindow("issueUpdate","scripts/updateIssue-script.php",$issue,$code,
			"Are you sure you want to move the selected issue to 'In Progress' ?","targetPlace","In_Progress");
			
			displayConfirmationWindow("issueTesting","scripts/updateIssue-script.php",$issue,$code,
			"Are you sure you want to move the selected issue to 'Testing' ?","targetPlace","Testing");
			
			displayConfirmationWindow("issueAbandon","scripts/updateIssue-script.php",$issue,$code,
			"Are you sure you want to abandon the selected issue ?","targetPlace","Abandoned");
			
			displayConfirmationWindow("issueComplete","scripts/updateIssue-script.php",$issue,$code,
			"Are you sure you want to mark the issue as Completed ?","targetPlace","Completed");
					
						
			echo "<div class='confirm' id='editWindow' style= 'display:none;'>";
			echo "<form action = 'scripts/editIssue-script.php' method='post'>";
				echo "<input type='hidden' name='issueId' value=".$_GET["selectedIssue"].">";
				echo "<input type='hidden' name='projectCode' value=".$code.">";
				echo "<input class='bigger-custom-input' type='text' style='margin-left:-0.5ex;' name='issueTitle' value='".$issue['issueTitle']."' placeholder = 'Issue Title'><br>";
				echo "<p style='margin-bottom: 1ex;'>Issue Priority: </p>";
				echo "<select name='issuePriority' id='account_type'>";
						echo "<option value=''>Select an option</option>";
						if($issue["issuePriority"]==1)
						{
							echo "<option selected='selected' value='1'>Low</option>";
							echo "<option value='2'>Medium</option>";
							echo "<option value='3'>High</option>";
						}
						else if($issue["issuePriority"]==2)
						{
							echo "<option value='1'>Low</option>";
							echo "<option selected='selected' value='2'>Medium</option>";
							echo "<option value='3'>High</option>";
						}
						else if($issue["issuePriority"]==3)
						{
							echo "<option value='1'>Low</option>";
							echo "<option value='2'>Medium</option>";
							echo "<option selected='selected' value='3'>High</option>";
						}
						

				echo "</select>";
				echo "<br><br>";
				echo "<textarea class='details' name='issueDetails' rows='5' placeholder = 'Details'>".$issue["issueDetails"]."</textarea><br>";
				
				echo "<button class='create_button' type='text' name='submit'>Apply</button>";
				echo "</form>";
					
			echo "</div>";
			
			echo "<div id='infoWindow' >";
				echo"<p class='issueInfoFields'>Status <br> ".$issue["issueStatus"]."</p>";
				echo "<p class='issueInfoFields'>Created by <br>".$issue['issueCreatedBy']."</p>"; 
				echo "<p class='issueInfoFields'>Developed by <br> ".$issue["issueDevelopedBy"]."</p>";
				echo "<div style='max-width: 600px;'>";
				echo "<pre class='issueDetail'>Details: <br> ".wordwrap($issue["issueDetails"])."<pre>";
				echo "</div>";
			echo "</div>";
		}
		if(isset($_GET["selectedIssue"]) && !issueBelongsToProject($con, $_GET["selectedIssue"], $code))
		{
			echo "<p class='noneSelected'>Issue with id=".$_GET["selectedIssue"]." not found.</p>";
			
		}
		echo "</div>";
	  ?>
	
	
	</div>
	</div>
	</body>
	
<html>