<?php

/** ***********************************************************************************************************************
 * TO DO LIST
1. Check if it is a relational database

2. Select target table

3. Select an unique key -> this attribute must not be repeated in the file

4. Check if the user wants to use a matching key -> this is a value that exists in all rows of the table

5. Check datatypes, nullable fields, field size and foreign keys.

6. Create validations for these properties

7. First validation for errors. Loop through all file rows and validate all fields. Foreign keys must also be validated

Concerning FK, check if it is possible to insert new values in the referenced table. If not, throw error.

In the end of the validation, the script must show all errors found in the csv file.

8. After the validation, each row must be inserted along with the foreign key values
 
 ****************************************************************************************************************************/

/**
 * This page will hold the login to the importer tool
 * Every user must have an active account to login. The password must be renewed from time to time if the account is 
 * permanently active
 */

/**
 * Display warnings
 * 
 * 1 - Display rules to import new products
 * 2 - Is it important to distinguish between economato and external products?
 * 3 - Allow different delete options depending on the target table
 * 4 - Develop index page
 * 6 - Develop different delete options according with the above tables but always allow a more general delete (FOR ANY TABLE)
 * 7 - New options available
 * 		- Introduce option to import directly with the foreign key id not the value
 * 		- Display table headers according with the chosen table
 * 		- Display rejected characters
 * 		- Create specific rule for economato
 */




?>