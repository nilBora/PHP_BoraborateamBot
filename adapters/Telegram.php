<?php

namespace Bot\Adapters;

use TelegramBot\Api\Client as BotClient;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update as BotUpdateType;


class Telegram extends AbstractBot implements IBot
{
    private $_bot;
    private $_db;
    
    public function init($db)
    {
        $this->_db = $db;
        $token = $this->getOption("TELEGRAM_TOKEN");
        $this->_bot = new BotClient($token);
        
        $this->_initCommands();
        
        $this->_bot->run();
    } // end init
    
    private function _initCommands()
    {
        $commandName = $this->_getCommandNameByRequest();
        
        $method = "doSend".ucfirst($commandName)."Command";
        if (!method_exists($this, $method)) {
            throw new \Exception("Undefined Command");
        }
        
        $this->$method();
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
        $instance = &$this;
        $bot->command('msg', function ($message) use ($bot, $instance) {
            $text = $message->getText();
            $param = str_replace('/msg ', '', $text);
            
            
            $data = explode("|", $param);
            
            $time = array_pop($data);
            $date = date("Y-m-d H:i:s", strtotime($time));
            $msg = array_pop($data);
            $answer = $date;
            
//            $dataStore = json_decode(file_get_contents(STORE_PATH), true);
//            if (!$dataStore) {
//                $dataStore = [];
//            }
//            $idUser = $message->getChat()->getId();
//
//            $dataStore[date("Y-m-d", strtotime($time))][$date][$idUser] = $msg;
//
//            file_put_contents(STORE_PATH, json_encode($dataStore));
            
            $instance->_addMessage($message, $date, $msg);
            
            $bot->sendMessage($message->getChat()->getId(), $answer);
        });
    } // end doSendMsgCommand
    
    private function _addMessage($message, $date, $msg)
    {
        $data = [
            'userId' => $message->getChat()->getId(),
            'date'   => $date,
            'msg'    => $msg
        ];
        
        $values = [
            'type'  => 'telegram',
            'data'  => json_encode($data),
            'date_send' => date("Y-m-d", strtotime($date)),
            'cdate' => date("Y-m-d H:i:s")
        ];
        
        $this->_db->insert('queue', $values);
    }
    
    private function _getCommandNameByRequest()
    {
        $data = BotApi::jsonValidate(
            $this->_bot->getRawBody(),
            true
        );
        
        $dataResponse = BotUpdateType::fromResponse($data);
        
        $message = $dataResponse->getMessage();
        
        if (!$message) {
            throw new \Exception(__('Bad Request From Telegram'));
        }
        
        $text = $message->getText();
        
        preg_match(BotClient::REGEXP, $text, $matches);
        
        if (empty($matches[1])) {
            return false;
        }
        
        return filter_var($matches[1], FILTER_SANITIZE_STRING);
    } //end _getCommandNameByRequest
}

