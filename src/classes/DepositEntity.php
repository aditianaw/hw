<?php

class DepositEntity
{
    protected $id;
	protected $depot_id;
	protected $waktu;
    protected $nilai_deposit;
	protected $owner;
   

    /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct(array $data) {
        // no id if we're creating
        if(isset($data['id'])) {
            $this->id = $data['id'];
        }
        $this->waktu = $data['waktu'];
        $this->nilai_deposit = $data['nilai_deposit'];
        $this->depot_id = $data['depot_id'];
	    $this->owner = $data['owner'];
    }

	public function getId() {
        return $this->id;
    }

    public function getWaktu() {
        return $this->waktu;
    }

    public function getNilai_deposit() {
        return $this->nilai_deposit;
    }

    public function getDepot_id() {
        return $this->depot_id;
    }
	
	public function getOwner() {
        return $this->owner;
    }

  
}
