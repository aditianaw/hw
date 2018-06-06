<?php

class DepotEntity
{
    protected $id;
    protected $lonlat;  
	protected $owner;
	protected $install_date;
	protected $saldo;

    /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct(array $data) {
        if(isset($data['id'])) {
            $this->id = $data['id'];
        }
        $this->lonlat = $data['lonlat']; 
		$this->owner = $data['owner'];
		$this->install_date = $data['install_date'];
   		$this->saldo = $data['saldo'];
	}

    public function getId() {
        return $this->id;
    }
	public function getLonlat() {
        return $this->lonlat;
    }
	public function getOwner() {
        return $this->owner;
    }
	public function getInstall_date() {
        return $this->install_date;
    }
	public function getSaldo() {
        return $this->saldo;
    }
	
}
