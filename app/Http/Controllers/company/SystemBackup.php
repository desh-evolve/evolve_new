<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once(Environment::getBasePath() .'classes/upload/fileupload.class.php');

if ( !$permission->Check('company','enabled')
		OR !( $permission->Check('wage','view') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}


$smarty->assign('title', TTi18n::gettext($title = 'System Bacup')); // See index.php
BreadCrumb::setCrumb($title);


extract	(FormVariables::GetVariables(
										array	(
												'action',
                                                                                    'backup_data',
                                                                                    'upload_file',
												'page',
												'sort_column',
												'sort_order',
												'ids'
												) ) );


URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
                  										) );

  //print_r($_FILES);exit;	

$action = Misc::findSubmitButton();
switch ($action) {
    
    	case 'submit':

            $filename="";
                if(isset($backup_data['backup_name']) && !empty($backup_data['backup_name']) ){
                   
                    $filename = $backup_data['backup_name'].'.sql';
                }
                else{
		    $filename='database_backup_'.date('G_a_m_d_y').'.sql';
                }
                
                 $command = 'mysqldump '.$config_vars['database']['database_name'].' --password='.$config_vars['database']['password'].' --user='.$config_vars['database']['user'].' --single-transaction >../backups/'.$filename;
                 
                 $result=exec($command,$output);

                 if($output==''){ echo "Done"; }
                 else {     print_r($output); }
                 

		break;
        case 'download':
                
            $file='../backups/'.$backup_data['select_file'];
            
            if (!empty($file) && file_exists($file)) {
                
                  header('Content-type: application/octet-stream');
   	          header('Content-Length: '.filesize($file));
                  header('Content-Disposition: attachment; filename='.basename($file));
                  
                  
                    readfile($file);
                    exit;
            }

		break;
        case 'delete':
            
            $file_delete='../backups/'.$backup_data['select_file'];
            
            unlink($file_delete);

		break;
        case  'restore':
            
                $file_restore='../backups/'.$backup_data['select_file'];
            
                $command =  'mysqlimport -h '.$config_vars['database']['host'] .' -u '.$config_vars['database']['user'].' -p'.$config_vars['database']['password'].' '.$config_vars['database']['database_name'].' < '.$file_restore;
                
                
                $result=exec($command,$output);

                 if($output==''){ echo "Done"; }
                 else {     
                 
                    // print_r($output); 
                     
                     foreach($output as $text_error){
                         
                       //  echo $text_error."\r\n";
                     }
                     
                 }
               // restoreDatabaseTables($config_vars['database']['host'],$config_vars['database']['user'],$config_vars['database']['password'],$config_vars['database']['database_name'],$file_restore);
            break;
            
         case  'upload':
             
             $upload = new fileupload();
             
             $temp_file_path = $_FILES['upload_file']['tmp_name'];//this is temperary file storage path
             $original_file_path = $_FILES['upload_file']['name'];//this is original file path
             
            
             $target_dir = "../backups/";
             $target_file = $target_dir . basename($_FILES["upload_file"]["name"]);
             
             
            $extention = pathinfo($original_file_path, PATHINFO_EXTENSION);
             
            if($extention == 'sql'){
                if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $target_file)) {
                   // echo "The file ". basename( $_FILES["upload_file"]["name"]). " has been uploaded.";
                } else {
                  //  echo "Sorry, there was an error uploading your file.".$target_file;
                }
            }
              
              
             
           
             break;
        default:
    
            break;
    
    

    
    
  }
  
  
  function restoreDatabaseTables($dbHost, $dbUsername, $dbPassword, $dbName, $filePath){
    // Connect & select the database
    $db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName); 

    // Temporary variable, used to store current query
    $templine = '';
    
    // Read in entire file
    $lines = file($filePath);
    
    $error = '';
    
    // Loop through each line
    foreach ($lines as $line){
        // Skip it if it's a comment
        if(substr($line, 0, 2) == '--' || $line == ''){
            continue;
        }
        
        // Add this line to the current segment
        $templine .= $line;
        
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';'){
            // Perform the query
            if(!$db->query($templine)){
                $error .= 'Error performing query "<b>' . $templine . '</b>": ' . $db->error . '<br /><br />';
            }
            
            // Reset temp variable to empty
            $templine = '';
        }
    }
    return !empty($error)?$error:true;
}




        $dh = opendir('../backups/');
       
    	while (($file = readdir($dh)) !== false)
		$ar_files[] = $file;
	closedir($dh);

                rsort($ar_files);
                $opt_files = "";
                foreach ($ar_files as $file)
                    if (preg_match("/.sql(.zip|.gz)?$/", $file))
                            $all_files[$file] = $file;
                    
        $smarty->assign_by_ref('all_files', $all_files);
                        
  
$smarty->display('company/SystemBackup.tpl');
echo 'fooo';


?>