<?php

/**
 * Manage the LDAP connection
 */
class ldapIGCBooking {

	/**
	 * Constructor
	 */	   
	function ldapIGCBooking(){
		//ldap connection parameters
		$this->ldapURI="ldap.igc.gulbenkian.pt";
		$this->userBase="cn=users,dc=igc,dc=gulbenkian,dc=pt";
		//$this->groupBase="cn=o,dc=igc,dc=gulbenkian,dc=pt";
		// connection handle
		$this->con=false;		
	}	   
	
	public function getldapURI(){	return $this->ldapURI;}
	
	/**
	 * Connect to ldap server, return connection handle
	 */
	function connect(){
	    try{
	    	$this->con = ldap_connect($this->ldapURI);
	    } catch (Exception $e){
	    	echo $e->getMessage();
	    }
	    if ($this->con){
	    	// php ldaps:// only works for LDAPv3
	    	ldap_set_option($this->con, LDAP_OPT_PROTOCOL_VERSION, 3); 
	    }
	    return $this->con;
	}
		   
	/**
	 * Try a successfull bind with ldap and user creds
	 */
	function bind($username,$pass){
		// remove spaces from login
		$login=trim($username);
		// do not allow anonymous binds!
		if($login=="") return false;
		$auth=ldap_bind($this->con, sprintf("uid=%s,%s",$login,$this->userBase), $pass); 
	    return $auth; 
	}

	/**
	 * Get ldap user admins
	 */
	function calAdmins($username){
		// remove spaces from login
		$login=trim($username);
		// check for admin ACL member
        if(in_array($login,$this->ldapUserACL)) return 1;
		// check if the user is a member of admin groups in LDAP
		$res=ldap_search($this->con,$this->groupBase,"memberUid=$login",array("cn",));
		$ents=ldap_get_entries($this->con,$res);
		for($g=0;$g<$ents['count'];$g++){
			if(in_array($g,$this->ldapGroupACL)) return 1;
		}	
		return 0;
	}

    /**
     * update db admin status
     */
    function updateAdmin($login,$flag){
        $sql=sprintf("UPDATE cal_user_info SET admin=%d WHERE user_login='%s'", 
                mysql_escape_string($flag),
                mysql_escape_string($login));
    	$res=mysql_query($sql) or die("SQL error: ".mysql_error()." ".$sql);    
    }
	
	/**
	 * Get ldap res perms
	 */
	function resPerms($username){
		return "A";
	}
    
    /**
     * get resource info from DB
     */
    function getResInfo(){
        $sql=sprintf("SELECT * FROM cal_resource WHERE res_id =%d", 
                mysql_escape_string($res_id));
    	$res=mysql_query($sql) or die("SQL error: ".mysql_error()." ".$sql);
    	return mysql_fetch_assoc($res);       
    }
        
    
    /**
     * get user data from db; 
     */
    function getUserInfo($login){
        // get user props for this resource
        $sql=sprintf("SELECT * FROM cal_user_info WHERE user_login='%s'",
                mysql_escape_string($login));
    	$res=mysql_query($sql) or die( "Err in mysql: ".mysql_error()." ".$sql);
        return mysql_fetch_assoc($res);        
    }

    /**
     * set user data to db;
     */
    function setUserInfo($login,$adminFlag){
        $sql=sprintf("INSERT INTO `cal_user_info` (`user_login` ,`user_pwd` ,`pwd_change` ,`admin`) VALUES ('%s', '%s', '%d', '%d')",
                mysql_escape_string($login),
                mysql_escape_string(randomString(45)),
                mysql_escape_string("-1"),
                mysql_escape_string($adminFlag));
        $res=mysql_query($sql)or die( "Err in mysql: ".mysql_error()." ".$sql);

    }


    /**
     * get user cal permissions
     */
    function getUserCalPerm($login,$res_id){
        // get user props for this resource
        $sql=sprintf("SELECT * FROM cal_perm WHERE perm_res_id =%d AND perm_login='%s'",
                mysql_escape_string($res_id),
                mysql_escape_string($login));
    	$res=mysql_query($sql)or die( "Err in mysql: ".mysql_error()." ".$sql);  
        return mysql_fetch_assoc($res);        
    }

    /**
     * set user cal permissions
     */
    function setUserCalPerm($login,$res_id,$adminFlag){
        $sql=sprintf("INSERT INTO `cal_perm` (`perm_login` , `perm_res_id` , `perm_type`) VALUES ('%s', '%s', '%s')",
                mysql_escape_string($login),
                mysql_escape_string($res_id),
                mysql_escape_string($adminFlag));
        $res=mysql_query($sql)or die( "Err in mysql: ".mysql_error()." ".$sql);;
    }
    
    
};

/**
 * Random string to fake passwords
 * @return 
 */
function randomString($len){
    $clist = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890$!#";
    $range=strlen($clist);
    for($j = 0; $j < $range; $j++){ 
        $random.=$clist[rand(0,$range)];
    }
    return $random;
}



