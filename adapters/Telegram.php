<?php

namespace Bot\Adapters;

use TelegramBot\Api\Client as BotClient;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update as BotUpdateType;


class Telegram implements IBot
{
    private $_bot;
    
    public function init()
    {
        $this->_bot = new BotClient(TELEGRAM_TOKEN);
        
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
        $bot->command('msg', function ($message) use ($bot) {
            $text = $message->getText();
            $param = str_replace('/msg ', '', $text);
        
        
            $data = explode("|", $param);
        
            $time = array_pop($data);
            $date = date("Y-m-d H:i:s", strtotime($time));
        
            $answer = $date;
            
            $dataStore = json_decode(file_get_contents(STORE_PATH), true);
            if (!$dataStore) {
                $dataStore = [];
            }
            $idUser = $message->getChat()->getId();
            $dataStore[date("Y-m-d", strtotime($time))][$date][$idUser] = array_pop($data);
        
            file_put_contents(STORE_PATH, json_encode($dataStore));
        
        
            $bot->sendMessage($message->getChat()->getId(), $answer);
        });
    } // end doSendMsgCommand
    
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
