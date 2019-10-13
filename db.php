<?php

function answer($string,$mysqli){
     $flag=false;
    $msg=null;
    if (mysqli_connect_errno()) { 
    printf("Подключение невозможно: %s\n", mysqli_connect_error()); 
    exit(); 
    } 
    
    if ($stmt = $mysqli->prepare("SELECT max(id) FROM dialog WHERE id")) { 
    $stmt->execute(); 
    $stmt->bind_result($col1); 
    while ($stmt->fetch()) { 
        $maxid = $col1;
    } 
    $stmt->close(); 
    }
    if($maxid!=null)
    for ($id = 0; $id <= $maxid; $id++) {
         if ($stmt = $mysqli->prepare("SELECT id,input FROM dialog WHERE id={$id}")) { 
            $stmt->execute(); 
            $stmt->bind_result($idexpress, $input); 
                    while ($stmt->fetch()) { 
                    $idex=$idexpress;
                    $msg = $input;
            } 
            $stmt->close(); 
             }
    $sim = similar_text($string, $msg, $perc);
    $perc = round($perc);     
        if($perc>76){
            if ($stmt = $mysqli->prepare("SELECT output FROM dialog WHERE id={$idex}")) { 
                $stmt->execute(); 
                $stmt->bind_result($output); 
                        while ($stmt->fetch()) { 
                        $ans = $output;
                } 
                $stmt->close(); 
            }
            if(!$ans==0){
                $stmt = $mysqli->prepare("UPDATE dialog SET `freq`=`freq`+1 WHERE id='{$idex}'"); 
                $stmt->bind_param('d', $idex); 
                $stmt->execute(); 
                $stmt->close();
                
            } else $ans='none';
            return $ans;
        }
    }
       try {
           if(!$string==null){
            $stmt = $mysqli->prepare("INSERT INTO dialog VALUES (0, ?, '0', 0)"); 
            $stmt->bind_param('s', $string); 
            $stmt->execute(); 
            $stmt->close(); 
            
          }    
        } 
        catch(Exception $e){
            echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        }
     
    return 'none';
}
