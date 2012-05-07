<?php
	require_once("commonCode.php");
	
	// ***************************
	// Requests made by the client
	if(isset($_POST['action'])){
		if(!isset($_POST['resource'])){
			throw new Exception('Resource not specified');
		}
		
		$userId = getUserId($_POST['userLogin'], $_POST['userPass']);
		// Need this comparison in case $userId = 0.... Thats php for you...
		if($userId === false){
			throw new Exception('Wrong username or password');
		}
	
		$isResp = isResp($userId, $_POST['resource']);
		if(!hasPermission($userId, $_POST['resource']) && $isResp === false){
			throw new Exception('User doesn\'t have permission for this action');
		}
	
		switch($_POST['action']){
			case "itemInsertHtml": // Regular user view
				$json->html = itemInsertHtml($userId, $_POST['resource'], $isResp);
			break;
			
			case "itemManagementHtml": // Manager user view
				$entries = null;
				if(isset($_POST['usePostEntries']) && json_decode($_POST['usePostEntries'])){
					$entries = $_POST['entries'];
				}
				$json->html = itemManagementHtml($userId, $_POST['resource'], $entries);
			break;
			
			case "associateEntriesAndItems": // Entry and item association
				$json->message = associateEntriesAndItems($userId, $_POST['resource'], $_POST['entries'], json_decode($_POST['items'], true));
			break;
			
			case "itemInsertOrRemove":
				if(isset($_POST['items']) && isset($_POST['remove'])){
					$json->message = itemInsertOrRemove($_POST['items'], $userId, $_POST['resource'], json_decode($_POST['remove']));
					$json->selectOptions = getItems($userId, $_POST['resource']);
				}
			break;
			
			case "emailUsersFromItems":
				$json->mailToUsers = emailUsersFromItems($_POST['items']);
			break;
			
			case "updateSubmittedList":
				if($isResp === false){
					throw new Exception('User doesn\'t have permission for this action');
				}
				$json->selectOptions = updateSubmittedList($_POST['asUser'], $_POST['resource']);
			break;
		}
		// $json->success = true;
		echo json_encode($json);
		exit;
	}
	
	// *************************
	// Html generation goes here
	echo "<link href='../agendo/css/item.css' rel='stylesheet' type='text/css' />";
	echo "<script type='text/javascript' src='../agendo/js/item.js'></script>";
	
	echo "<div id='itemInterfaceDiv'>";
	echo "</div>";
	

	// *********
	// Functions
	function itemInsertHtml($userId, $resource, $isResp){
		$html = "";
		$sql = "select resource_name from resource where resource_id = :0";
		$prep = dbHelp::query($sql, array($resource));
		$row = dbHelp::fetchRowByIndex($prep);
		
		$html .= "<a onclick='closeitemInsertDiv();' onmouseover='this.style.cursor=\"pointer\"' style='position:absolute;top:0px;right:5px;color:#bb3322;font-size:16px;'>";
			$html .= "x";
		$html .= "</a>";
		
		
		if($isResp !== false){
			$html .= "<label style='float:left;'>";
				$html .= "Insert/remove item as user:";
			$html .= "</label>";

			$html .= "<br>";
		
			$sql = "select distinct user_id, user_firstname, user_lastname, user_login, user_login from user, permissions where user_id = permissions_user and permissions_resource = :0 || user_id = :1";
			$prep = dbHelp::query($sql, array($resource, $userId));
			$html .= "<select id='asUserList' style='float:left;width:200px;' onchange='fillSubmittedListFromUser();'>";
				while($row = dbHelp::fetchRowByIndex($prep)){
					$html .= "<option value='".$row[0]."'>";
					$html .= $row[1]." ".$row[2]." (".$row[3].")";
					$html .= "</option>";
				}
			$html .= "</select>";
			
			$html .= "<br>";
			$html .= "<br>";
		}

		$html .= "<label style='float:left;'>";
			$html .= "New item for ".$row[0].": ";
		$html .= "</label>";
		
		$html .= "<br>";
		
		$html .= "<input class='inputText' type='text' id='itemName' style='float:left;'/>";
		$html .= "
			<input type='button' 
				class='buttons'
				id='insertItemButton' 
				value='Insert Item' 
				onclick='itemInsertOrRemove()' 
				title='Inserts the new item'
				style='float:right;'
			/>
		";
	
		$html .= "<br>";
		$html .= "<br>";
		
		$html .= "<label style='float:left;'>";
			$html .= "Submitted items not yet used:";
		$html .= "</label>";

		$html .= "<br>";
		
		// List where the submitted items will show
		$html .= "<select class='selectList' id='submittedItems' multiple='multiple' style='float:left;'>";
		$html .= "</select>";
		
		// Fills the list with items submitted by the user, not used in a sequencing session
		$html .= "
			<script>
				fillItemsListOptions(".json_encode(getItems($userId, $resource)).");
			</script>
		";
		
		$html .= "
			<input type='button' 
				class='buttons'
				id='removeItemButton' 
				value='Remove Item' 
				onclick='itemInsertOrRemove(true)' 
				title='Removes the selected item(s)'
				style='float:right;'
			/>
		";
		
		if($isResp !== false){
			$html .= "<br>";
			
			$html .= "
				<input type='button' 
					class='buttons'
					id='backButton' 
					value='Back' 
					onclick='back();' 
					title='Return to the entry association screen'
					style='float:right;margin-top:20px;'
				/>
			";
		}
		
		// Div that shows the possible states
		$html .= "<div style='position:absolute;bottom:20px;right:20px;text-align:right;'>";
			$heightAndWidth = 14;
			$topMargin = 5;
			$rightMargin = 5;
			$sql = "select item_state_id, item_state_name from item_state where item_state_id != 3"; // remove the unecessary states in the query here
			$prep = dbHelp::query($sql);
			while($row = dbHelp::fetchRowByIndex($prep)){
				$html .= "<div class='optionState".$row[0]."' style='float:right;width:".$heightAndWidth."px;height:".$heightAndWidth."px;margin-top:".$topMargin."px;'></div>";
				$html .= "<label style='float:right;margin-right:".$rightMargin."px;margin-top:".$topMargin."px;'>".$row[1]."</label>";
				$html .= "<br>";
			}
		$html .= "</div>";
		
		return $html;
	}
	
	function itemInsertOrRemove($items, $userId, $resource, $remove){
		if(!isset($items) || sizeOf($items) == 0){
			throw new Exception("No items were detected");
		}
		else{
			if($remove){
				foreach($items as $item){
					$sql = "delete from item where item_id = :0 and item_user = :1 and item_resource = :2";
					$prep = dbHelp::query($sql, array($item, $userId, $resource));
				}
				return "Item(s) Removed";
			}
			else{
				$sql = "insert into item values(null, :0, :1, 1, :2)";
				$prep = dbHelp::query($sql, array($items[0], $userId, $resource));
				return "Item Inserted";
			}
		}
	}
	
	function itemManagementHtml($userId, $resource, $entries = null){
		$html .= "<a onclick='closeitemInsertDiv();' onmouseover='this.style.cursor=\"pointer\"' style='position:absolute;top:0px;right:5px;color:#bb3322;font-size:16px;'>";
			$html .= "x";
		$html .= "</a>";
		
		$html .= "<div style='text-align:left;float:left;'>";
			$html .= "<label>";
				$html .= "Available items:";
			$html .= "</label>";
			
			$html .= "<br>";

			$html .= "<select class='selectList' id='submittedItems' multiple='multiple'>";
			$html .= "</select>";
		$html .= "</div>";
				$html .= "&nbsp";
	
		$html .= "<div style='margin-top:60px;margin-left:20px;margin-right:20px;float:left;'>";
			$html .= "<input type='button' value=' <<- ' title='Remove all' onclick='swapAll(\"lockedItems\", \"submittedItems\");'/>";
			$html .= "&nbsp";
			$html .= "<input type='button' value=' ->> ' title='Add all' onclick='swapAll(\"submittedItems\", \"lockedItems\");'/>";
			$html .= "<br>";
			$html .= "<input type='button' value=' <- ' title='Remove selected' onclick='swapSelected(\"lockedItems\", \"submittedItems\");'/>";
			$html .= "&nbsp";
			$html .= "<input type='button' value=' -> ' title='Add selected' onclick='swapSelected(\"submittedItems\", \"lockedItems\");'/>";
		$html .= "</div>";

		$html .= "<div style='float:left;text-align:left;'>";
			$html .= "<label>";
				$html .= "Locked items:";
			$html .= "</label>";
			
			$html .= "<br>";

			$html .= "<select class='selectList' id='lockedItems' multiple='multiple' onclick=''>";
			$html .= "</select>";
		$html .= "</div>";

		
		$html .= "<div style='float:right;'>";
			$html .= "<input class='buttons' type='button' id='saveButton' value='Save' title='Save current sample list' onClick='saveItemList();' style='margin-top:14px;'/>";
			$html .= "<br>";
			$html .= "<input class='buttons' type='button' id='cancelButton' value='Cancel' title='Cancels the operation, closes the menu and deletes the entry(ies)' onClick='closeitemInsertDiv(true);' style='margin-top:5px;'/>";
			$html .= "<br>";
			$html .= "<input class='buttons' type='button' id='newItemButton' value='New Item' onclick='newItem();' style='margin-top:44px;'/>";
			$html .= "<br>";
			$html .= "<input class='buttons' type='button' id='emailButton' onclick='emailUsersFromItems();' value='Email' title='Email the users of the locked items' style='margin-top:5px;'/>";

		$html .= "</div>";
	
		$html .= "<br>";
		
		$html .= "<div style='float:left;text-align:left;width:100%;margin-top:20px;'>";
			$html .= "<label>";		
				$html .= "Email subject:";		
			$html .= "</label>";
			
			$html .= "<br>";
			
			$html .= "<input type='input' id='emailSubject' value='Sequencing' style='width:100%;'/>"; // get the value from the bd later?

			$html .= "<br>";
			$html .= "<br>";
			
			$html .= "<label>";		
				$html .= "Email text:";		
			$html .= "</label>";
			
			$html .= "<br>";

			$html .= "<textarea style='width:100%;' id='emailBody'>";
				$html .= "The samples you submitted were used.";
			$html .= "</textarea>";
		$html .= "</div>";

		// Fills the list with items submitted by the user, not used in a sequencing session
		$html .= "
			<script>
				fillItemsListOptions(".json_encode(getItems($userId, $resource, $entries)).");
			</script>
		";
		
		return $html;
	}
	
	
	// Returns a user id either from the session var or from the login and password text fields
	function getUserId($userLogin, $userPass){
		if(isset($_SESSION['user_id'])){
			return $_SESSION['user_id'];
		}
		
		$sql = "select user_id from ".dbHelp::getSchemaName().".user where user_login = :0 and user_passwd = :1";
		$prep = dbHelp::query($sql, array($userLogin, cryptPassword($userPass)));
		if(isset($userLogin) && isset($userPass) && dbHelp::numberOfRows($prep) > 0){
			$row = dbHelp::fetchRowByIndex($prep);
			return $row[0];
		}
		
		return false;
	}
	
	// Returns the items id, name and state associated to the user and resource (only items with the state 1 and 2)
	function getItems($userId, $resource, $entriesArray = null){
		$itemsArray = array();
		
			$select = "
				select 
					item_id
					, item_name
					, item_state 
			";
			
			$from = "
				from 
					item
			";
			
			$where = "
				where
					item_resource = :0
			";
			
			$orderBy = "
				order by 
					item_state
					, item_name
			";
			
		if(isResp($userId, $resource) !== false){
			$inData = dbHelp::inDataFromArray($entriesArray);
			if($inData !== false){
				$from .= "
					, item_assoc
				";
				$where = "
					where 
						item_assoc_entry in ".$inData['inData']." 
						and item_resource = :".($inData['size'])."
				";
				$entriesArray[] = $resource; // pushing the resource in the entries array to then send to PDO for the sql query
				$sql = $select." ".$from." ".$where." ".$orderBy;
				$prep = dbHelp::query($sql, $entriesArray);
			}
			else{
				$where .= "
					and item_state = 1
				";
				$sql = $select." ".$from." ".$where." ".$orderBy;
				$prep = dbHelp::query($sql, array($resource));
			}
			// $sql = "select item_id, item_name, item_state from item where item_resource = :0 ".$extraSql." order by item_state, item_name";
		}
		else{
			$where = "
				where
					item_user = :0
					and item_resource = :1
					and item_state in (1, 2)
			";

			// $sql = "select item_id, item_name, item_state from item where item_user = :0 and item_resource = :1 and item_state in (1, 2) order by item_state, item_name";
			$sql = $select." ".$from." ".$where." ".$orderBy;
			$prep = dbHelp::query($sql, array($userId, $resource));
		}

		while($row = dbHelp::fetchRowByIndex($prep)){
			$itemsArray[$row[0]] = array('name' => $row[1], 'state' => $row[2]);
		}
		
		return $itemsArray;
	}
	
	// Makes the association between items and an entry, an entry as x number of items and changes the state of the items to locked
	function associateEntriesAndItems($userId, $resource, $entries, $items){
		if(isResp($userId, $resource) === false){
			throw new Exception("User not allowed for this operation");
		}
		
		if(!isset($items)){
			throw new Exception("There are no items");
		}

		foreach($entries as $entry){ // all entries
			foreach($items as $itemState=>$itemArray){ // all item states
				foreach($itemArray as $item){ // all item that belong to a state
					updateOrInsertItemOnEntry($entry, json_decode($item, true), $itemState);
				}
			}
		}
		
		return "Items saved";
	}
	
	// Aux function that either updates the state of an item or inserts it in the assoc table associated to an entry
	function updateOrInsertItemOnEntry($entry, $item, $itemState){
		$itemIdEntryArray = array($entry, $item['id']);
		$stateArray = array('locked' => 2, 'submitted' => 1);
		$sql = "select item_assoc_id, item_state from item, item_assoc where item_assoc_entry = :0 and item_assoc_item = :1";
		$prep = dbHelp::query($sql, $itemIdEntryArray);
		$row = dbHelp::fetchRowByIndex($prep);
			// wtf($row[0]."  ".$itemState."  ".$item['id'], 'a');
		if(!isset($row[0]) && $stateArray[$itemState] == 2){
			$sql = "insert into item_assoc values(null, :0, :1)";
			dbHelp::query($sql, $itemIdEntryArray);
			
			$sql = "update item set item_state = 2 where item_id = :0";
			dbHelp::query($sql, array($item['id']));
		}
		elseif(isset($row[0]) && $stateArray[$itemState] == 1){
			// changes the item status to available
			$sqlItem = "update item set item_state = 1 where item_id = :0 and item_state != 1";
			$prepItem = dbHelp::query($sqlItem, array($item['id']));
			
			// deletes the association of that item with the entry
			$sqlItem = "delete from item_assoc where item_assoc_id = :0";
			$prepItem = dbHelp::query($sqlItem, array($row[0]));
		}
	}
	
	function emailUsersFromItems($items){
		if(!$inSql = dbHelp::inDataFromArray($items)){
			throw new Exception("Couldn't get items data");
		}
		
		// wtf($inSql['inData']);
		$mailToUsers = "";
		$sql = "select distinct user_email from item, user where user_id = item_user and item_id in ".$inSql['inData'];
		$prep = dbHelp::query($sql, $items);
		if(dbHelp::numberOfRows($prep) > 0){
			$row = dbHelp::fetchRowByIndex($prep);
			$mailToUsers .= $row[0];
			
			while($row = dbHelp::fetchRowByIndex($prep)){
				$mailToUsers .= ",".$row[0];
			}
			// wtf($mailToUsers);
			return $mailToUsers;
			// echo json_encode($json);
		}
		else{
			throw new Exception("No users found");
		}
		
	}
	
	function updateSubmittedList($asUser, $resource){
		return getItems($asUser, $resource);
	}
?>