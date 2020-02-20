<?php

namespace Bot\Adapters;

use Bot\Database\PdoObjectAdapter;
use Exception;
use Viber\Bot;
use Viber\Api\Sender;

class Viber implements IBot
{
    private $_bot;
    
    public function init()
    {
        $apiKey = VIBER_TOKEN;
        
        $botSender = new Sender([
            'name' => 'Whois bot!',
            'avatar' => 'https://developers.viber.com/img/favicon.ico',
        ]);
        
        $db = new PdoObjectAdapter();
        $this->_bot = new Bot(['token' => $apiKey]);
        $bot = &$this->_bot;
        $bot
            ->onConversation(function ($event) use ($bot, $botSender) {
                return (new \Viber\Api\Message\Text())
                    ->setSender($botSender)
                    ->setText("Can i help you??");
            })
            ->onText('|msg .*|si', function ($event) use ($bot, $botSender, $db) {
                $data = [
                    'userId' => $event->getSender()->getId(),
                    'date' => '2020-02-01 21:11'
                ];

                $values = [
                    'type' => 'viber',
                    'data' => json_encode($data),
                    'cdate' => '2020-02-01'
                ];

                 $db->insert('queue', $values);
                
                $bot->getClient()->sendMessage(
                    (new \Viber\Api\Message\Text())
                        ->setSender($botSender)
                        ->setReceiver($event->getSender()->getId())
                        ->setText("I do not know )")
                );
            });
        
        $this->_bot->run();
    } // end init
    
    private function _initCommands()
    {
        $this->doSendMsgCommand();
//        $commandName = $this->_getCommandNameByRequest();
//
//        $method = "doSend".ucfirst($commandName)."Command";
//        if (!method_exists($this, $method)) {
//            throw new \Exception("Undefined Command");
//        }
//
//        $this->$method();
    } // end _initCommands
    
    public function doSendHelpCommand()
    {
        $bot = $this->_bot;
        $bot->command('help', function ($message) use ($bot) {
            $answer = 'Команды: /help - вывод справки /msg';
            $bot->sendMessage($message->getChat()->getId(), $answer);
        });
    } // end doSendHelpCommand
    
    public function doSendStartCommand()
    {
        $bot = $this->_bot;
        $bot->command('start', function ($message) use ($bot) {
            $answer = 'Добро пожаловать!';
            $bot->sendMessage($message->getChat()->getId(), $answer);
        });
    } // end doSendStartCommand
    
    public function doSendMsgCommand()
    {
        $bot = $this->_bot;
       // $db = new PdoObjectAdapter();
        $botSender = new Sender([
            'name' => 'Whois bot!',
            'avatar' => 'https://developers.viber.com/img/favicon.ico',
        ]);
        
        $bot->onText('|msg .*\|.* |si', function ($event) use ($bot, $botSender) {
            //$data = explode("|", $param);
            
            $data = [
                'userId' => $event->getSender()->getId(),
                'date' => '2020-02-01 21:11'
            ];
            
            $values = [
                'type' => 'viber',
                'data' => json_encode($data),
                'cdate' => '2020-02-01'
            ];
    
           // $db->insert('queue', $values);
            
            $bot->getClient()->sendMessage(
                (new \Viber\Api\Message\Text())
                    ->setSender($botSender)
                    ->setReceiver($event->getSender()->getId())
                    ->setText("Ok")
            );
            
        })->run();
    } // end doSendMsgCommand
}

