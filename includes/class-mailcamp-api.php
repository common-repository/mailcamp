<?php
/**
 * Fired during plugin activation
 *
 * @link       https://mailcamp.nl
 * @since      1.0.0
 *
 * @package Mailcamp
 * @subpackage Mailcamp/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all api calls that are necessary to let this plugin communicate with the MailCamp software
 *
 * @since      1.0.0
 * @package Mailcamp
 * @subpackage Mailcamp/includes
 * @author Silas de Rooy <silasderooy@gmail.com>
 */
class MailCamp_Api {

	public $api_credentials = [];
	public $connection = false;
	private $xml_data;
	private $xml_timeout = 3;
	public $result;
	public $test = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param array $api_credentials The credentials we need to make the calls
	 */
	public function __construct( array $api_credentials = [] ) {
		$this->api_credentials = $api_credentials;
		$this->result          = new stdClass();
	}

	/**
	 * Connect to MailCamp
     *
     * @since 1.0.0
	 * @return stdClass
	 */
	public function connection() {
		$this->xml_data = '
                        <requesttype>authentication</requesttype>
                        <requestmethod>xmlapitest</requestmethod>
                        <details>
                        </details>';

		$this->execute();

		if ( $this->result->status === true ) {
			$this->connection   = true;
			$this->result->data = __( 'connected', 'mailcamp' );
		}

		return $this->result;
	}

	/**
	 * Get List details by listid
	 *
     * @since 1.0.0
	 * @param int $listid
	 * @return mixed
	 */
	public function listDetails( $listid = 0 ) {
		$this->xml_data = '
            <requesttype>lists</requesttype>
            <requestmethod>GetLists</requestmethod>
            <details>
                <lists>' . $listid . '</lists>
            </details>
        ';

		$this->execute();

		return $this->result->data;
	}

	/**
	 * Get all lists
	 *
     * @since 1.0.0
	 * @return stdClass
	 */
	public function lists( $start = 0, $perpage = 500 ) {
		$this->xml_data = '
            <requesttype>user</requesttype>
            <requestmethod>Getlists</requestmethod>
            <details>
                <lists/>
                <sortinfo/>
                <lists />
                <countonly/>
                <start>' . $start . '</start>
                <perpage>' . $perpage . '</perpage>
            </details>
        ';

		$this->execute();

		return $this->result->data;
	}

	/**
	 * Get lists by subscriberid
	 *
     * @since 1.0.0
	 * @param int $subscriberid
	 * @return mixed
	 */
	public function loadSubscriberList( $subscriberid = 0 ) {
		$this->xml_data = '
            <requesttype>subscribers</requesttype>
            <requestmethod>LoadSubscriberList</requestmethod>
            <details>
                 <subscriberid>' . $subscriberid . '</subscriberid>
                 <returnonly>false</returnonly>
                 <activeonly>true</activeonly>
                 <include_customfields>true</include_customfields>
            </details>
        ';

		$this->execute();

		return $this->result->data;
	}

	/**
	 * mail function for sending newsletters via MailCamp API
	 *
     * @since 1.0.0
	 * @updated 1.5.12
	 * @param string $to_email
	 * @param $from_details
	 * @param $html
	 * @return mixed
	 */
	public function mc_mail( $to_email = '', $details = [], $html = '' ) {

		$this->xml_data = '
            <requesttype>subscribers</requesttype>
			<requestmethod>SendMail</requestmethod>
			<details>
				<to_details>
					<subscriberid>' . $details['subscriberid'] . '</subscriberid>
					<listid>' . $details['listid'] . '</listid>
				</to_details>
				<from_details>
					<from_name>' . $details['from_name'] . '</from_name>
					<from_address>' . $details['from_address'] . '</from_address>
					<replyto>' . $details['replyto'] . '</replyto>
					<bounce_address>' . $details['bounce_address'] . '</bounce_address>
				</from_details>
				<newsletter_details>
					<base64>true</base64>
					<htmlbody><![CDATA[
                  	' . urlencode(base64_encode($html)) . '
            		]]></htmlbody>
            		<textbody><![CDATA[
                  	' . urlencode(base64_encode($this->generateTextVersion( $html ))) . '
            		]]></textbody>
					<subject>' . $from_details['subject'] . '</subject>
				</newsletter_details>
				<type>h</type>
				<userid>1</userid>
			</details>
        ';
		$this->execute();

		return $this->result->data;
	}

	/**
     * @since 1.0.0
	 * @param string $email
	 * @param int $listid
	 * @return mixed
	 */
	public function getSubscriberFromList( $email = '', $listid = 0 ) {
		$this->xml_data = '
				<requesttype>subscribers</requesttype>
				<requestmethod>IsSubscriberOnList</requestmethod>
				<details>
					<emailaddress>' . $email . '</emailaddress>
					<listids>' . $listid . '</listids>
				</details>';

		$this->execute();

		return $this->result->data;
	}


	/**
	 * insert subscribers (dynamically add custom fields xml)
	 *
     * @since 1.0.0
	 * @param array $post_data
	 * @return mixed
	 */
	public function insertSubscriber( array $post_data = [] , string $confirmed = 'no') {
		$this->xml_data = '
            <requesttype>subscribers</requesttype>
            <requestmethod>AddSubscriberToList</requestmethod>
            <details>
                <emailaddress>' . $post_data['email'] . '</emailaddress>
                <mailinglist>' . $post_data['listid'] . '</mailinglist>
                <format>html</format>
                <confirmed>' . $confirmed . '</confirmed>
                <ipaddress>' . ( @$_SERVER['REMOTE_ADDR'] ?: 0 ) . '</ipaddress>
                <subscribedate>' . time() . '</subscribedate>
				<add_to_autoresponders>true</add_to_autoresponders>';

		unset( $post_data['listid'], $post_data['email'] );
		$customfields = $post_data;

		if ( ! empty( $customfields ) ) {
			$this->xml_data .= '
                <customfields>';

			foreach ( $customfields as $fieldid => $field ) {
				$this->xml_data .= '
                    <item>
                        <fieldid>' . $fieldid . '</fieldid>';
				if ( is_array( $field ) ) {
					foreach ( $field as $fld ) {
						$this->xml_data .= '
                        <value>' . $fld . '</value>';
					}
				} else {
					$this->xml_data .= '
                        <value>' . $field . '</value>';
				}
				$this->xml_data .= '                      
                    </item>';
			}
			$this->xml_data .= '
                </customfields>';
		}
		$this->xml_data .= '        
            </details>
        ';

		$this->execute();

		return $this->result->data;
	}

	/**
	 * Get archives by listid
	 *
     * @since 1.0.0
	 * @param int $listid
	 * @param int $count
	 * @return mixed
	 */
	public function getArchives( $listid = 0, $count = 3 ) {
		$this->xml_data = '
        <requesttype>lists</requesttype>  
        <requestmethod>GetArchives</requestmethod>  
        <details>
        	<listid>' . $listid . '</listid>
        	<num_to_retrieve>' . $count . '</num_to_retrieve>
			<include_body>false</include_body>
        </details>
        ';
		
		$this->xml_timeout = 10;

		$this->execute();

		return $this->result;
	}

	/**
	 * Get custom fields that are mapped to the list by listid
	 *
     * @since 1.0.0
	 * @param $listid
	 * @return mixed
	 */
	public function fields( $listid ) {
		$this->xml_data = '
            <requesttype>lists</requesttype>
            <requestmethod>GetCustomFields</requestmethod>
            <details>
                <listids>' . $listid . '</listids>
            </details>
        ';

		$this->execute();

		return $this->result->data;
	}

	/**
	 * Final execution after creating the xml request data
	 *
	 * @since 1.0.0
	 * @updated 1.5.7
	 */
	private function execute() {
		$this->result->status = false;
		$this->result->data   = '';

		$xml_data =
			'<xmlrequest>
            <username>' . $this->api_credentials['api_username'] . '</username>
            <usertoken>' . $this->api_credentials['api_token'] . '</usertoken>';
		$xml_data .= $this->xml_data;
		$xml_data .= '</xmlrequest>';

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->api_credentials['api_path'] );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FAILONERROR, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_POST, true );
		if ( ! ini_get( 'safe_mode' ) && ini_get( 'open_basedir' ) == '' ) {
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		}
		curl_setopt( $ch, CURLOPT_TIMEOUT, $this->xml_timeout );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, 'xml=' . $xml_data );

		$page_data = curl_exec( $ch );

		if ( curl_errno( $ch ) > 0 ) {
			$this->result->status = false;
			$this->result->data   = curl_error( $ch );
		} else {

			$result = @simplexml_load_string( $page_data );

			if ( $result && isset( $result->data ) ) {
				$this->result->status = true;
				$this->result->data   = $result->data;
			}

			if ( $result && isset( $result->errormessage ) ) {
				$this->result->status = false;
				$this->result->data   = $result->errormessage;
			}

		}

	}

	/**
	 * generates a text version from HTML
	 * set href from anchor before its title
	 *
     * @since 1.0.0
	 * @param $html
	 * @return string
	 */
	public static function generateTextVersion( $html ) {
		// empty the styles, this will not be stripped in the strip_tags function
		$html = preg_replace( "/<style\\b[^>]*>(.*?)<\\/style>/s", "<style></style>", $html );
		// strip tags except anchor tags
		$text = strip_tags( substr( $html, strpos( $html, '</style>' ) + 8 ), '<a>' );
		// remove to many lines and replace them into two lines
		$text = preg_replace( '/(?:(?:\r\n|\r|\n)\s*){2}/s', "\n\n", $text );
		// clear the last non-breaking space Entity Number &#160;
		$text = str_replace( '&#160;', ' ', $text );

		$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		$needle = 'cc10ff64350c6f30eda66376876baa8e';
		$result = '';
		/* Loop trough all lines */
		foreach ( preg_split( "/((\r?\n)|(\r\n?))/", $text ) as $line ) {

			$stripped_line = explode( ' ', strip_tags( $line ) );

			$stripped_line = array_filter( $stripped_line, function ( $val ) {
				return ( $val || is_numeric( $val ) );
			} );

			if ( ! empty( $stripped_line ) ) {

				/* if has anchor tag in text line */
				if ( preg_match_all( "/$regexp/siU", $line, $matches ) ) {

					/* replace open anchor tag with needle */
					$line     = trim( preg_replace( '/< a.*?>|<a.*?>/', $needle, $line ) );
					$add_word = '';
					$c        = 0;
					/* loop trough all words in current sentence */
					foreach ( explode( ' ', strip_tags( $line ) ) as $word ) {
						/* if needle that has to be replaced exist.. */
						if ( strpos( $word, $needle ) !== false ) {
							/* if anchor link does not contain hashtag # */
							if ( strpos( $matches[2][ $c ], '#' ) === false ) {
								/* replace the needle with the link from the anchor tag */
								$add_word .= str_replace( $needle, '[' . $matches[2][ $c ] . ']', $word ) . ' ';
							}
							$c ++;
						} else {
							$add_word .= $word . ' ';
						}
					}

					$result .= trim( $add_word ) . PHP_EOL . PHP_EOL;

				} else {

					$result .= trim( strip_tags( $line ) ) . PHP_EOL . PHP_EOL;

				}

			}

		}

		return $result;
	}

}
