<?php

if (!array_key_exists('SAMLResponse', $_REQUEST)) {
	throw new SimpleSAML_Error_BadRequest('Missing SAMLResponse parameter.');
}

if (!array_key_exists('TARGET', $_REQUEST)) {
	throw new SimpleSAML_Error_BadRequest('Missing TARGET parameter.');
}


$state = SimpleSAML_Auth_State::loadState($_REQUEST['TARGET'], 'saml:sp:ssosent-saml1');

/* Find authentication source. */
assert('array_key_exists("saml:sp:AuthId", $state)');
$sourceId = $state['saml:sp:AuthId'];

$source = SimpleSAML_Auth_Source::getById($sourceId);
if ($source === NULL) {
	throw new SimpleSAML_Error_Exception('Could not find authentication source with id ' . $sourceId);
}
if (!($source instanceof sspmod_saml_Auth_Source_SP)) {
	throw new SimpleSAML_Error_Exception('Source type changed?');
}


$responseXML = $_REQUEST['SAMLResponse'];
$responseXML = base64_decode($responseXML);

$response = new SimpleSAML_XML_Shib13_AuthnResponse();
$response->setXML($responseXML);

$response->validate();

$idp = $response->getIssuer();
$attributes = $response->getAttributes();

$logoutState = array(
	'saml:logout:Type' => 'saml1'
	);
$state['LogoutState'] = $logoutState;

$source->handleResponse($state, $idp, $attributes);
assert('FALSE');

?>