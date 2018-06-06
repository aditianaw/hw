<?php

class DepositMapper extends Mapper
{
	
    public function getDeposit() {
        $sql = "SELECT ds.id, ds.waktu, ds.depot_id,ds.nilai_deposit, d.owner
            from deposit ds
            join depot d on (ds.depot_id = d.id)";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new DepositEntity($row);
        }
        return $results;
    }
	
	public function getDepositById($deposit_id) {
        $sql = "SELECT ds.id, ds.waktu, ds.depot_id,ds.nilai_deposit, d.owner
            from deposit ds
            join depot d on (ds.depot_id = d.id) where ds.id= :deposit_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["deposit_id" => $deposit_id]);
        return new DepositEntity($stmt->fetch());
    }
	
	public function save(DepositEntity $deposit) {
        $sql = "insert into deposit
            (id, depot_id,waktu, nilai_deposit) values
            (:id,:depot_id,now(),:nilai_deposit)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
			"id" 					=> $deposit->getId(),	
			"depot_id" 				=> $deposit->getDepot_id(),	
			"nilai_deposit"			=> $deposit->getNilai_deposit(),	
        ]);
		
        if(!$result) {
            throw new Exception("could not save record");
        }
    }
	
	public function delete(DepositEntity $deposit) {
		 $sql = "DELETE FROM deposit WHERE id=:id";
		 
				 $stmt = $this->db->prepare($sql);
				 $result = $stmt->execute([
				 "id" => $deposit->getId(),
				 ]);
				$db = null;
		}
	
	
}
