<?php
   class Generic {
        protected $pdo;

        function __construct($pdo){
            
            $this->pdo = $pdo;
    
        
        }  
        
    public function checkInput($var)
    {
        $var = htmlspecialchars($var);
        $var = trim($var);
        $var = stripcslashes($var);
        return $var;
    }

    

    public function count_distinct ($tables, $column){
        $stmt = $this->pdo->prepare("SELECT DISTINCT $column FROM $tables");
        $stmt->execute();
        $multi =  $stmt->rowCount();
      
        return $multi; 
    }

    public function count_all ($tables){
        $stmt = $this->pdo->prepare("SELECT * FROM $tables");
        $stmt->execute();
        $multi =  $stmt->rowCount();
      
        return $multi; 
    }


    public function get_All ($tables, $sort, $order){
        $stmt = $this->pdo->prepare("SELECT * FROM $tables ORDER BY $sort $order");
        $stmt->execute();
        $multi = $stmt->fetchAll(PDO::FETCH_OBJ);
      
        return $multi; 
    }


    public function get_count($table, $fields = array(), $sort, $order)
    {
        $columns = '';
        $i       = 1;

        foreach($fields as $name => $value){
            $columns .= "`{$name}` = :{$name}";
             if($i < count($fields)){
                $columns .= ' AND ';
            }
            $i++;
        }
        $sql = "SELECT * FROM {$table}  WHERE {$columns} ORDER BY $sort $order";
        if($stmt = $this->pdo->prepare($sql))
        {
            foreach($fields as $key => $value)
            {
                $stmt->bindValue(':'.$key, $value);
            } 
              $stmt->execute();
              $count = $stmt->rowCount();
       // $single = $stmt->fetch(PDO::FETCH_OBJ);
        }
        return $count; 
    }

    public function get_single($table, $fields = array(), $sort='', $order='')
    {
        $columns = '';
        $i       = 1;

        foreach($fields as $name => $value){
            $columns .= "`{$name}` = :{$name}";
             if($i < count($fields)){
                $columns .= ' AND ';
            }
            $i++;
        }
        $sql = "SELECT * FROM {$table}  WHERE {$columns} ORDER BY $sort $order";
        if($stmt = $this->pdo->prepare($sql))
        {
            foreach($fields as $key => $value)
            {
                $stmt->bindValue(':'.$key, $value);
            } 
              $stmt->execute();
        $single = $stmt->fetch(PDO::FETCH_OBJ);
        }
        return $single; 
    }


    
    public function get_multi($table, $fields = array(), $sort, $order)
    {
        $columns = '';
        $i       = 1;
        
        foreach($fields as $name => $value){
            $columns .= "`{$name}` = :{$name}";
             if($i < count($fields)){
                $columns .= ' AND ';
            }
            $i++;
        }
        $sql = "SELECT * FROM {$table}  WHERE {$columns} ORDER BY $sort $order";
        if($stmt = $this->pdo->prepare($sql))
        {
            foreach($fields as $key => $value)
            {
                $stmt->bindValue(':'.$key, $value);
            } 
              $stmt->execute();
        $single = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        return $single; 
    }


 

    public function create($table, $fields = array())
    {
        $columns = implode(',', array_keys($fields));
        $values  = ':' . implode(', :', array_keys($fields));
        $sql     = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
        if ($stmt = $this->pdo->prepare($sql)) {
            foreach ($fields as $key => $data) {
                $stmt->bindValue(':' . $key, $data);
            }
            $stmt->execute();
            return $this->pdo->lastInsertId();
        }
    }
    
        

    public function updates($table, $checkid, $fields = array()) {
        $columns = '';
        $i = 1;

        foreach($fields as $name => $value) {
            $columns .= "`{$name}` = :{$name}";
            if($i < count($fields)) {
                $columns .= ', ';
            }
            $i++;
        }
        $sql = "UPDATE {$table} SET {$columns} WHERE `student_id` = {$checkid}";
        if($stmt = $this->pdo->prepare($sql)) {
            foreach($fields as $key => $value) {
                $stmt->bindValue(':'.$key, $value);
            }
            //var_dump($sql);
            $stmt -> execute();
        }
    }


    public function update($table, $where, $id, $fields = array()){
        $columns = '';
        $i       = 1;

        foreach($fields as $name => $value){
            $columns .= "`{$name}` = :{$name}";
             if($i < count($fields)){
                $columns .= ', ';
            }
            $i++;
        }
         $sql = "UPDATE {$table} SET {$columns} WHERE {$where} = {$id}";
        if($stmt = $this->pdo->prepare($sql)){
            foreach($fields as $key => $value){
                $stmt->bindValue(':'.$key, $value);
            }
            //var_dump($sql);
          if($stmt->execute()){
            return true;
          }else{
              return false;
          }
        }
    }

   
    public function delete($table, $array) {
        $sql = "DELETE FROM `{$table}`";
        $where = "WHERE ";
        foreach($array as $name=>$value){
            $sql.="{$where} `{$name}` = :{$name}";
            $where = " AND ";
        }
        if($stmt = $this->pdo->prepare($sql)){
            foreach($array as $name => $value){
                $stmt->bindvalue(':'.$name, $value);
            }
            $excex = $stmt->execute();
            if($excex){
                return true;
            }
        }
         
    }




    }
?>
