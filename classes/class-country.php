<?php 

class Country {

    private string $abbrev;

    public function __construct(
        private string $name
    ) {
        $this->set_abbrev();
    }

	/**
	 * @return string
	 */
	public function get_abbrev(): string {
		return $this->abbrev;
	}
	
	/**
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	public function set_name(string $name) {
		$this->name = $name;
	}

    /**
     *
     */
    public function set_abbrev() {
        $abbrev1 = "";
        switch ($this->get_name()) {
            case 'Canada':
                $abbrev1 = "CA";
                break;
            case "United States":
                $abbrev1 = "US";
                break;
            case 'Columbia':
                $abbrev1 = "CO";
                break;
            case 'Argentina':
                $abbrev1 = "AR";
                break;
            case 'Peru':
                $abbrev1 = "PR";
                break;
            case 'Brazil':
                $abbrev1 = "BR";
                break;
            case 'Ecuador':
                $abbrev1 = "EC";
                break;
            case 'Poland':
                $abbrev1 = "PL";
                break;
        }
        $this->abbrev = $abbrev1;
    }

}

?>