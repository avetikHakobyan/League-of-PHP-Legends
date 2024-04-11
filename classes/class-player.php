<?php 

class Player {

	public function __construct(
		private string $first_name,
		private string $last_name,
		private string $email,
		private string $city,
		private Country $country,
		private string $password,
		private string $professional
	) {

	}

	/**
	 * @return string
	 */
	public function get_first_name(): string {
		return $this->first_name;
	}
	
	/**
	 * @param string $first_name
	 */
	public function set_first_name(string $first_name) {
		$this->first_name = $first_name;
	}

	/**
	 * @return string
	 */
	public function get_last_name(): string {
		return $this->last_name;
	}
	
	/**
	 * @param string $last_name
	 */
	public function set_last_name(string $last_name) {
		$this->last_name = $last_name;
	}

	/**
	 * @return string
	 */
	public function get_email(): string {
		return $this->email;
	}
	
	/**
	 * @param string $email
	 */
	public function set_email(string $email) {
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function get_city(): string {
		return $this->city;
	}
	
	/**
	 * @param string $city
	 */
	public function set_city(string $city) {
		$this->city = $city;
	}

	/**
	 * @return string
	 */
	public function get_country(): Country {
		return $this->country;
	}
	
	/**
	 * @param Country $country
	 */
	public function set_country(Country $country) {
		$this->country = $country;
	}

	/**
	 * @return string
	 */
	public function get_password(): string {
		return $this->password;
	}
	
	/**
	 * @param string $password
	 */
	public function set_password(string $password) {
		$this->password = $password;
	}
	
	/**
	 * @return string
	 */
	public function get_professional(): string {
		return $this->professional;
	}
	
	/**
	 * @param string $professional
	 */
	public function set_professional(string $professional) {
		$this->professional = $professional;
	}
}

?>