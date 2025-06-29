<?php
// Create and listen to server connection
$EOF = 3;
$port = 8081;
$serverSocket = createServerConnection($port);
socket_listen($serverSocket) or die("Unable to start server, exiting!");
echo "Server now running on port $port\n";

// Check for incoming messages or connect/disconnect requests
$listOfConnectedClients = [];
$clientRooms = [];
$connectedClientsHandshakes = [];
// handshake is a mechanisom by which the server and the connecting clients introduce each other,
// authenticate and establish how they want to communicate/the rules.

do {
	$clientsWithData = waitForIncomingMessageFromClients($listOfConnectedClients, $serverSocket);

	// Do we have a connection request - i.e. serverSocket is one of the clients with data?
	if (in_array($serverSocket, $clientsWithData)) {
		$newSocket = socket_accept($serverSocket);
		// handshake is a mechanisom by which the server and the connecting clients introduce each other,
		// authenticate and establish how they want to communicate/the rules.
		if (performHandshake($newSocket)) {
			$listOfConnectedClients[] = $newSocket;
			echo "connected. #clients: " . count($listOfConnectedClients) . "\n";
		} else {
			disconnectClient($newSocket, $listOfConnectedClients, $connectedClientsHandshakes, $clientsWithData);
		}
	} else { // must be regular data message or disconnect message
		foreach ($clientsWithData as $clientSocket) {
			$len = @socket_recv($clientSocket, $buffer, 1024, 0); // read to eoln or 1024 bytes
			if ($len === false || $len == 0 || strlen($message = unmask($buffer)) > 0 && ord($message[0]) == $EOF) { // disconnecting client or error? If so, remove it from connected list
				disconnectClient($clientSocket, $listOfConnectedClients, $connectedClientsHandshakes, $clientsWithData);
			} else {
				if (!empty($message)) {
					echo "Received:>>$message<<\n";

					$data = json_decode($message, true);

					if (!$data || !isset($data['type'])) {
						echo "Invalid message format.\n";
						continue;
					}

					switch ($data['type']) {
						case 'new_room':
							// Broadcast new room to all clients
							foreach ($listOfConnectedClients as $client) {
								sendMessage($client, json_encode([
									'type' => 'new_room',
									'chatroomName' => $data['name'] ?? '',
									'locked' => $data['locked'] ?? false
								]));
							}
							break;

						case 'chat_message':
							// 1) Figure out which index (0, 1, 2, …) this $clientSocket occupies
							$senderId = array_search($clientSocket, $listOfConnectedClients, true);

							$clientId = array_search($clientSocket, $listOfConnectedClients, true);
							$clientRooms[$clientId] = $data['room'];
							$clientRooms[$senderId] = $data['room'];

							// 2) Make sure array_search found something, and that we actually have a room assigned
							if ($senderId >= 0) {
								if (array_key_exists($senderId, $clientRooms)) {
									// Only now is it safe to read $clientRooms[$senderId]
									$senderRoom = $clientRooms[$senderId];
									echo "senderRoom: {$senderRoom}\n";

									// 3) Broadcast to everyone who has the same room
									foreach ($listOfConnectedClients as $client) {
										$clientId = array_search($client, $listOfConnectedClients, true);
										if (
											$clientId !== 0
											&& array_key_exists($clientId, $clientRooms)
											&& $clientRooms[$clientId] === $senderRoom
										) {
											sendMessage($client, json_encode([
												'type' => 'chat_message',
												'room' => $senderRoom,
												'screenName' => $data['screenName'] ?? '',
												'message' => $data['message'] ?? ''
											]));
										}
									}
								} else {
									// The client is connected, but hasn’t joined a room yet. Print a warning.
									echo "client #{$senderId} has no entry in \$clientRooms.\n";
								}
							} else {
								// array_search returned false – somehow we didn’t find $clientSocket in our clients list.
								echo "senderId was false (client not in \$listOfConnectedClients).\n";
							}
							break;

						default:
							echo "Unknown message type: {$data['type']}\n";
					}
				}
			}
		}
	}
} while (true);



// Create server socket for others to connect to and communicate with
function createServerConnection($port, $host = 0)
{
	// Create TCP/IP streaming socket
	$serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	// Set option for the port to be reusable
	socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);
	// Bind the socket to $port and to the $host given. Default host is 0 i.e. localhost
	socket_bind($serverSocket, $host, $port);
	return $serverSocket;
}

// Wait for incoming message from clients. Note that message could be data or connection/disconnection request
// We check for incoming message usig the socket_select method.
// It takes several input/output parameters such as readSockets, writeSockets, and exceptionSockets
//
// The readSockets is a list of sockets to be checked for incoming input data.
// We add the server socket to the read list because connection requests are represented as a read on the server socket
// i.e. the server socket indicates read whenever a client tries to connect
// The other clients will indicate read-ready if message has been sent through them
//
// The writeSockets are typically set to empty because we assume all connected sockets are ready to receive data (write ready)
// However, if the socket is closed, it won't be write ready. We check for and remove closed sockets
//
// The exception socket is a list of sockets that you want monitored until an exception happens in any of them. Exception could be read past end of file. We set these to empty because such exceptions are handled elsewhere in the code or ignored
//
function waitForIncomingMessageFromClients($clients, $serverSocket)
{
	$readList = $clients;		// start with list of clients for read list
	$readList[] = $serverSocket;	// append server socket so we can also detect connect requests
	$writeList = $exceptionList = [];	// We use empty for these

	// Loop until a read, connect or disconnect request
	socket_select($readList, $writeList, $exceptionList, NULL);
	return $readList;
}

function disconnectClient($clientSocket, &$listOfConnectedClients, &$connectedClientsHandshakes, &$clientsWithData)
{
	if (($clientKey = array_search($clientSocket, $clientsWithData)) !== false) {	// find the index
		unset($clientsWithData[$clientKey]);				// zap it from the list
	}
	if (($clientKey = array_search($clientSocket, $listOfConnectedClients)) !== false) { // find the index
		unset($listOfConnectedClients[$clientKey]);			// zap it from the list
		unset($connectedClientsHandshakes[$clientKey]);			// zap it from the list
		echo "disconnected client\n";
	}
	socket_close($clientSocket);	// close the connection to it
}



// handshake is a mechanisom by which the server and the connecting clients introduce each other,
// authenticate and establish how they want to communicate/the rules.
function performHandshake($clientSocket)
{
	$data = @socket_recv($clientSocket, $headers, 1024, 0); // read to eoln or 1024 bytes
	if ($data === false || $data = 0)
		return false; // disconnected
	$headers = parseHeaders($headers);
	// this is not a strict handshake because we don't enforce that the Sec-WebSocket-Keys match, we only want to see the Key
	if (!isset($headers['Sec-WebSocket-Key']))
		return false;
	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
	$handshakeResponse = "HTTP/1.1 101 Switching Protocols\r\n" .
		"Upgrade: websocket\r\n" .
		"Connection: Upgrade\r\n" .
		"Sec-WebSocket-Accept: $secAccept\t\r\n\r\n";
	socket_write($clientSocket, $handshakeResponse, strlen($handshakeResponse));
	return true;
}

// Parse HTTP Headers		
function parseHeaders($headers)
{
	$headers = explode("\r\n", $headers);
	$headerArray = [];
	foreach ($headers as $header) {
		$parts = explode(": ", $header);
		if (count($parts) === 2) {
			$headerArray[$parts[0]] = $parts[1];
		}
	}
	return $headerArray;
}

// Masking and unmasking of the messages is highly recommend because websockets are implemented
// via the http connection. If the message is not masked it may be misinterpreted by the http server
// as a regular http message rather than a websocket message.
function unmask($payload)
{
	if (strlen($payload) == 0)
		return "";
	$length = ord($payload[1]) & 127;
	if ($length == 126) {
		$masks = substr($payload, 4, 4);
		$data = substr($payload, 8);
	} elseif ($length == 127) {
		$masks = substr($payload, 10, 4);
		$data = substr($payload, 14);
	} else {
		$masks = substr($payload, 2, 4);
		$data = substr($payload, 6);
	}
	$unmaskedtext = '';
	for ($i = 0; $i < strlen($data); ++$i) {
		$unmaskedtext .= $data[$i] ^ $masks[$i % 4];
	}
	return $unmaskedtext;
}

function mask($message)
{
	$frame = [];
	$frame[0] = 129;

	$length = strlen($message);
	if ($length <= 125) {
		$frame[1] = $length;
	} elseif ($length <= 65535) {
		$frame[1] = 126;
		$frame[2] = ($length >> 8) & 255;
		$frame[3] = $length & 255;
	} else {
		$frame[1] = 127;
		$frame[2] = ($length >> 56) & 255;
		$frame[3] = ($length >> 48) & 255;
		$frame[4] = ($length >> 40) & 255;
		$frame[5] = ($length >> 32) & 255;
		$frame[6] = ($length >> 24) & 255;
		$frame[7] = ($length >> 16) & 255;
		$frame[8] = ($length >> 8) & 255;
		$frame[9] = $length & 255;
	}

	foreach (str_split($message) as $char) {
		$frame[] = ord($char);
	}

	return implode(array_map('chr', $frame));
}

function sendMessage($clientSocket, $message)
{
	$message = mask($message);
	socket_write($clientSocket, $message, strlen($message));
}
