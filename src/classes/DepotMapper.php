<?php

class DepotMapper extends Mapper
{
    public function getDepot() {
        $sql = "SELECT *
            from depot order by id asc";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new DepotEntity($row);
        }
        return $results;
    }
	
	public function save(DepotEntity $depot) {
        $sql = "insert into depot
            (id, lonlat,owner,install_date,saldo) values
            (:id,:lonlat,:owner,:install_date,:saldo)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "id" 			=> $depot->getId(),	
			"lonlat" 		=> $depot->getLonlat(),
            "depot"			=> $depot->getOwner(),
			"install_date" 	=> $depot->getInstall_date(),
            "saldo" 		=> $depot->getSaldo(),
        ]);
		
        if(!$result) {
            throw new Exception("could not save record");
        }
    }

    public function getDepotById($depot_id) {
        $sql = "SELECT *
         from depot where id = :depot_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["depot_id" => $depot_id]);
        return new DepotEntity($stmt->fetch());
    }
	
	public function delete(DepotEntity $depot) {
		 $sql = "DELETE FROM depot WHERE id=:id";
		 
				 $stmt = $this->db->prepare($sql);
				 $result = $stmt->execute([
				 "id" => $depot->getId(),
				 ]);
				$db = null;
		}

	
}	
	

