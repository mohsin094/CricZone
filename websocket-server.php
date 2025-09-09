<?php

require_once __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Loop;
use React\Socket\SocketServer;
use React\Http\HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;
use React\Stream\WritableResourceStream;

class CricketWebSocketServer
{
    private $loop;
    private $clients = [];
    private $liveMatches = [];

    public function __construct()
    {
        $this->loop = Loop::get();
    }

    public function start($port = 8080)
    {
        $socket = new SocketServer("0.0.0.0:{$port}", [], $this->loop);
        $server = new HttpServer($this->loop, [$this, 'handleRequest']);
        $server->listen($socket);

        echo "WebSocket server running on port {$port}\n";

        // Start live match updates
        $this->startLiveMatchUpdates();

        $this->loop->run();
    }

    public function handleRequest(ServerRequestInterface $request)
    {
        $path = $request->getUri()->getPath();
        
        if ($path === '/ws') {
            return $this->handleWebSocketUpgrade($request);
        }
        
        if ($path === '/api/live-matches') {
            return $this->handleLiveMatchesAPI($request);
        }

        return new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'Not found']));
    }

    private function handleWebSocketUpgrade(ServerRequestInterface $request)
    {
        $connection = $request->getAttribute('connection');
        
        if (!$connection) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode(['error' => 'No connection']));
        }

        // Simple WebSocket upgrade
        $response = new Response(101, [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $this->generateAcceptKey($request->getHeaderLine('Sec-WebSocket-Key'))
        ]);

        // Handle WebSocket connection
        $this->handleWebSocketConnection($connection);

        return $response;
    }

    private function handleWebSocketConnection($connection)
    {
        $clientId = uniqid();
        $this->clients[$clientId] = $connection;

        echo "Client connected: {$clientId}\n";

        // Send current live matches
        $this->sendToClient($clientId, [
            'type' => 'live_matches',
            'data' => $this->liveMatches
        ]);

        // Handle client messages
        $connection->on('data', function ($data) use ($clientId) {
            $this->handleClientMessage($clientId, $data);
        });

        // Handle client disconnect
        $connection->on('close', function () use ($clientId) {
            unset($this->clients[$clientId]);
            echo "Client disconnected: {$clientId}\n";
        });
    }

    private function handleClientMessage($clientId, $data)
    {
        try {
            $message = json_decode($data, true);
            
            if ($message['type'] === 'subscribe_match') {
                $matchKey = $message['match_key'];
                // Store subscription for this client
                // Implementation depends on your needs
            }
        } catch (\Exception $e) {
            echo "Error handling client message: " . $e->getMessage() . "\n";
        }
    }

    private function sendToClient($clientId, $data)
    {
        if (isset($this->clients[$clientId])) {
            $message = json_encode($data);
            $this->clients[$clientId]->write($message);
        }
    }

    private function broadcastToAllClients($data)
    {
        $message = json_encode($data);
        
        foreach ($this->clients as $clientId => $connection) {
            try {
                $connection->write($message);
            } catch (\Exception $e) {
                echo "Error sending to client {$clientId}: " . $e->getMessage() . "\n";
                unset($this->clients[$clientId]);
            }
        }
    }

    private function handleLiveMatchesAPI(ServerRequestInterface $request)
    {
        return new Response(200, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*'
        ], json_encode([
            'live_matches' => $this->liveMatches,
            'timestamp' => time()
        ]));
    }

    private function startLiveMatchUpdates()
    {
        // Update live matches every 10 seconds
        $this->loop->addPeriodicTimer(10, function () {
            $this->updateLiveMatches();
        });
    }

    private function updateLiveMatches()
    {
        try {
            // This would typically call your Laravel application
            // For now, we'll simulate updates
            $this->liveMatches = $this->fetchLiveMatchesFromAPI();
            
            // Broadcast updates to all connected clients
            $this->broadcastToAllClients([
                'type' => 'live_matches_updated',
                'data' => $this->liveMatches,
                'timestamp' => time()
            ]);

            echo "Live matches updated and broadcasted to " . count($this->clients) . " clients\n";
        } catch (\Exception $e) {
            echo "Error updating live matches: " . $e->getMessage() . "\n";
        }
    }

    private function fetchLiveMatchesFromAPI()
    {
        // This would call your Laravel API endpoint
        // For now, return empty array
        return [];
    }

    private function generateAcceptKey($key)
    {
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        return $acceptKey;
    }
}

// Start the server
$server = new CricketWebSocketServer();
$server->start(8080);
