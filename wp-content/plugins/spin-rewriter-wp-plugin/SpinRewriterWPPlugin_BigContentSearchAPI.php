<?php

class SpinRewriterWPPlugin_BigContentSearchAPI {
	
	var $username;
	var $api_key;
	var $response;
	var $api_url;
	
	/*
	 * Big Content Search API constructor, complete with authentication.
	 * @param $email_address
	 * @param $api_key
	 */
	function SpinRewriterWPPlugin_BigContentSearchAPI($username, $api_key) {
		$this->username = $username;
		$this->api_key = $api_key;
		$this->api_url = "https://members.bigcontentsearch.com/api/";
	}
	
	/*
	 * Pings the API server to check if everything is OK.
	 */
	function ping() {
		$this->makeRequest("ping");
		return $this->parseResponse();
	}
	
	/*
	 * Searches the database for articles that match the given search term
	 * @param $term
	 */
	function search($term) {
		$data = array();
		$data['search_term'] = $term;
		$this->makeRequest("article_get_by_search_term", $data);
		return $this->parseResponse();
	}
	
	/*
	 * Searches the database for never-before-returned articles that match the given search term
	 * @param $term
	 * @param $ids_to_skip (array)
	 */
	function searchWithoutDuplicates($term, $ids_to_skip) {
		$data = array();
		$data['search_term'] = $term;
		$data['ids_to_skip'] = json_encode($ids_to_skip);
		$this->makeRequest("article_get_by_search_term", $data);
		return $this->parseResponse();
		
		/*
			Example response:
			
			$array['status_code']		= 0; 		(e.g. 100: no articles found, 110: daily download limit reached)

			$array['response']['text']	= article
			$array['response']['uid']	= unique ID	(e.g. "c39151fbbbe14c71a17c0e62de52a93b")
			$array['response']['title']	= title		(already included in the article)
			
			$array['error_msg']
		 */
	}
	
	/*
	 * Parses raw JSON response and returns a native PHP array.
	 */
	private function parseResponse() {
		return json_decode($this->response, true);
	}
	
	/*
	 * Sends a request to the Big Content Search API and saves the non-formatted response.
	 */
	private function makeRequest($command, $data = false) {
		$data_raw = "";
		$data_raw = $data_raw . "username" . "=" . urlencode($this->username) . "&";
		$data_raw = $data_raw . "api_key" . "=" . urlencode($this->api_key) . "&";

		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $value) {
				$data_raw = $data_raw . $key . "=" . urlencode($value) . "&";
			}
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_url . $command);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_raw);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$this->response = trim(curl_exec($ch));
		curl_close($ch);
	}
}

?>