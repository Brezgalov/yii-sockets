<?php

namespace app\helpers;

class SocketSender extends \yii\base\BaseObject
{
    protected $host = 'localhost';

    protected $port = 1985;

    protected $socket;

    public function __construct()
    {
        header('Content-Type: text/plain;'); //Мы будем выводить простой текст
        set_time_limit(0); //Скрипт должен работать постоянно
        ob_implicit_flush(); //Все echo должны сразу же выводиться
        if (($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
            //AF_INET - семейство протоколов
            //SOCK_STREAM - тип сокета
            //SOL_TCP - протокол
            echo "Ошибка создания сокета";
        }
        else {
            echo "Сокет создан\n";
        }
        $result = socket_connect($socket, $this->host, $this->port);
        if ($result === false) {
            echo "Ошибка при подключении к сокету";
        } else {
            echo "Подключение к сокету прошло успешно\n";
        }
        $out = socket_read($socket, 1024); //Читаем сообщение от сервера
        echo "Сообщение от сервера: $out.\n";
        $this->socket = $socket;
    }

    public function send($msg)
    {
        echo "Сообщение серверу: $msg\n";
        socket_write($this->socket, $msg, strlen($msg)); //Отправляем серверу сообщение
        $out = socket_read($this->socket, 1024); //Читаем сообщение от сервера
        echo "Сообщение от сервера: $out.\n"; //Выводим сообщение от сервера
    }

    public function close()
    {
        $this->send('exit');//Команда отключения
        echo "Соединение завершено\n";
        //Останавливаем работу с сокетом
        if (isset($this->socket)) {
            socket_close($this->socket);
            echo "Сокет успешно закрыт";
            unset($this->socket);
        }
    }
}