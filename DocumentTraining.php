<?php
    require_once 'Connection.php';
    class DocumentTraining extends Connection {

        public function addClass($name){
            try{
                $sql ="INSERT INTO class VALUES(NULL, :name)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':name', $name);
                $stmt->execute();
                $id = $this->conn->lastInsertId();
                if($id)
                    return $id;
                else 
                    return false;
            }catch(PDOException $e){
                echo "addClass :".$e->getMessage();
                return false;
            }
        }
        public function getClassId($name){
            try{
                $stmt = $this->conn->prepare("SELECT id_class FROM class WHERE class_name = :name");
                $stmt->bindParam(':name', $name);
                $stmt->execute();
                $id = $stmt->fetchColumn(0);
                return $id;
            }catch(PDOException $e){
                echo "getClassId : ".$e->getMessage();
                return false;
            }
        }
        public function addDoc($id_class, $title, $abstract){
            try{
                $sql = "INSERT INTO document_training VALUES(NULL, :id_class, :title, :abstract)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute(array(':id_class'=>$id_class,
                                    ':title'=>$title,
                                    ':abstract'=>$abstract));
                $id = $this->conn->lastInsertId('id_doc');
                if($id)
                    return $id;
                else 
                    return false;
            }catch(PDOException $e){
                echo "addDoc : ".$e->getMessage();
                return false;
            }
        }
        public function countClass(){
            try{            
                $stmt = $this->conn->prepare("SELECT C.class_name, COUNT(D.id_doc) AS count FROM class C LEFT JOIN document_training D ON C.id_class = D.id_class GROUP BY 1");
                $stmt->execute();
                while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $data[] = $r;
                }
                
                return $data;
            }catch(PDOException $e)
            {
                echo $e->getMessage();
                return false;
            }
        }
        public function generateTermFreq($id_doc, $terms){
            $count = array_count_values($terms);
            
            try{
                $sel_stmt = $this->conn->prepare("SELECT * FROM all_terms ORDER BY id_term ASC");
                $sel_stmt->execute();
                while($r = $sel_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $result[] = $r;
                }
                $ins_stmt = $this->conn->prepare("INSERT INTO term_freq VALUES(NULL, :id_doc, :id_term, :freq)");
                $this->conn->beginTransaction();
                foreach ($result as $key => $value) {
                    extract($value);
                    if(array_key_exists($term, $count)){
                        $freq = $count[$term];                        
                        $ins_stmt->execute(array(':id_doc'=>$id_doc, ':id_term'=>$id_term, ':freq'=>$freq));
                    }

                }
                $this->conn->commit();

                return true;
            }catch(PDOException $e){
                echo $e->getMessage();
                return false;
            }
        }
	}
?>