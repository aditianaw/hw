<?php

class DeliveryMapper extends Mapper
{
	
    public function getDelivery() {
        $sql = "SELECT dl.id, dl.waktu, dl.depot_id,dl.nilai, d.owner
            from delivery dl
            join depot d on (dl.depot_id = d.id)";
        $stmt = $this->db->query($sql);
        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new DeliveryEntity($row);
        }
        return $results;
    }
	
	public function getDeliveryById($delivery_id) {
        $sql = "SELECT dl.id, dl.waktu, dl.depot_id,dl.nilai, d.owner
            from delivery dl
            join depot d on (dl.depot_id = d.id) where dl.id= :delivery_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["delivery_id" => $delivery_id]);
        return new DeliveryEntity($stmt->fetch());
    }
	
	public function save(DeliveryEntity $delivery) {
        $sql = "insert into delivery
            (id, depot_id,nilai,waktu) values
            (:id,:depot_id,:nilai,now())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "id" 			=> $delivery->getId(),	
			"depot_id" 		=> $delivery->getDepot_id(),	
			"nilai"			=> $delivery->getNilai(),	
        ]);
        if(!$result) {
            throw new Exception("could not save record");
        }
    }
	
	public function delete(DeliveryEntity $delivery) {
		 $sql = "DELETE FROM delivery WHERE id=:id";
				 $stmt = $this->db->prepare($sql);
				 $result = $stmt->execute([
				 "id" => $delivery->getId(),
				 ]);
				$db = null;
	}
}
