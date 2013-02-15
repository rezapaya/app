<?php
/**
 * Created by JetBrains PhpStorm.
 * User: adam
 * Date: 31.01.13
 * Time: 12:08
 * To change this template use File | Settings | File Templates.
 */

class PandoraSDSObject implements JsonSerializable {

	const TYPE_LITERAL = 'literal';
	const TYPE_OBJECT = 'PandoraSDSObject';
	const TYPE_COLLECTION = 'array';

	protected $type = PandoraSDSObject::TYPE_COLLECTION;
	protected $subject;
	protected $value;

	public function setType( $type ) {
		if ( $type === static::TYPE_COLLECTION ) {
			$this->value = array();
		} else {
			$this->value = '';
		}
		$this->type = $type;
	}

	public function getType() {
		return $this->type;
	}

	public function setSubject( $subject ) {
		$this->subject = $subject;
	}

	public function getSubject() {
		if ( $this->subject ) {
			return $this->subject;
		}
	}

	public function setValue( $value ) {
		if ( $this->type === static::TYPE_COLLECTION ) {
			$this->value[] = $value;
		} else {
			$this->value = $value;
		}
	}

	public function getValue() {
		return $this->value;
	}

	public function getFlattenData() {
		return $this->jsonSerialize();
	}

	/**
	 * (PHP 5 >= 5.4.0)
	 * Serializes the object to a value that can be serialized natively by json_encode().
	 * @link http://docs.php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed Returns data which can be serialized by json_encode(), which is a value of any type other than a resource.
	 */
	function jsonSerialize() {
		if ( $this->type === static::TYPE_COLLECTION ) {
			if ( isset( $this->value ) ) {
				$returnValue = array();
				foreach ( $this->value as $val ) {
					if ( $val->getType() === static::TYPE_LITERAL ) {
						if ( $val->getSubject() ) {
							$returnValue[ $val->getSubject() ] = $val->getValue();
						} else {
							$returnValue[] = $val->getValue();
						}
					} else {
						if ( $val->getSubject() ) {
							$returnValue[ $val->getSubject() ] = $val->jsonSerialize();
						} else {
							$returnValue[] = $val->jsonSerialize();
						}
					}
				}
				return $returnValue;
			} else {
				return new stdClass();
			}
		} elseif ( $this->type === static::TYPE_OBJECT  ) {
			if ( is_object( $this->value ) ) {
				return array ( $this->value->getSubject() => $this->value->getValue() );
			} else {
				return new stdClass();
			}
		} elseif ( $this->type === static::TYPE_LITERAL ) {
			return array( $this->subject => $this->value );
		}
	}
}
