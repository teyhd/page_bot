<?php

function answer($string,$mysqli){
     $flag=false;
    $msg=null;
    if (mysqli_connect_errno()) { 
    printf("Подключение невозможно: %s\n", mysqli_connect_error()); 
    exit(); 
    } 
    
     if ($stmt = $mysqli->prepare("SELECT id,input FROM dialog WHERE 1")) { 
        $stmt->execute(); 
        $stmt->bind_result($idexpress, $input); 
        while ($stmt->fetch()) { 
            $idex=$idexpress;
            $msg = $input;
            
            $sim = similar_text($string, $msg, $perc);
            $perc = round($perc);   
            
             if($perc>76){
                    $ans = get($idex);
                    if($ans!=null) return $ans;
                }
        } 
        $stmt->close(); 
         }
  
       try {
           if(!$string==null){
            $stmt = $mysqli->prepare("INSERT INTO dialog VALUES (0, ?, '0', 0)"); 
            $stmt->bind_param('s', $string); 
            $stmt->execute(); 
            $stmt->close(); 
            $time = date('H:i:s');
            logs($fd,"\t[$time]\x1b[35m Добавлено выражение: \x1b[0m \n");
            logs($fd,"\t[$time]\x1b[36m [$string] \x1b[0m\n\n");
          }    
        } 
        catch(Exception $e){
            echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        }
     
    return 'none';
}
function get($idex){
        $mysqli1 = new mysqli(DB_HOST, DB_LOGIN, DB_PASS, 'wall_bot');
        if ($stmt1 = $mysqli1->prepare("SELECT output FROM dialog WHERE id={$idex}")) {
        $stmt1->execute(); 
        $stmt1->bind_result($output); 
                while ($stmt1->fetch()) { 
                $ans = $output;
                
        } 
        $stmt1->close(); 
    }
    
    if(!$ans==0){
        $stmt2 = $mysqli1->prepare("UPDATE dialog SET `freq`=`freq`+1 WHERE id='{$idex}'"); 
        $stmt2->execute(); 
        $stmt2->close();
        
    } else $ans='none';
    return $ans;
}

