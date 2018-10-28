<?php

namespace app\helpers;

class SocketServer extends \yii\base\BaseObject
{
    protected $host = 'localhost';

    protected $port = 1985;

    public function start($handler)
    {
        if (empty($this->port) || empty($this->host)) {
            throw new \Exception('Недостаточно данных для запуска сервера.');
        }

        header('Content-Type: text/plain;'); //Мы будем выводить простой текст
        set_time_limit(0); //Скрипт должен работать постоянно
        ob_implicit_flush(); //Все echo должны сразу же отправляться клиенту
        $address = $this->host; //Адрес работы сервера
        $port = $this->port; //Порт работы сервера (лучше какой-нибудь редкоиспользуемый)
        if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
            //AF_INET - семейство протоколов
            //SOCK_STREAM - тип сокета
            //SOL_TCP - протокол
            echo "Ошибка создания сокета\n";
        }
        else {
            echo "Сокет создан\n";
        }
        //Связываем дескриптор сокета с указанным адресом и портом
        if (($ret = socket_bind($sock, $address, $port)) < 0) {
            echo "Ошибка связи сокета с адресом и портом\n";
        }
        else {
            echo "Сокет успешно связан с адресом и портом\n";
        }
        //Начинаем прослушивание сокета (максимум 5 одновременных соединений)
        if (($ret = socket_listen($sock, 5)) < 0) {
            echo "Ошибка при попытке прослушивания сокета\n";
        }
        else {
            echo "Ждём подключение клиента\n";
        }
        do {
            //Принимаем соединение с сокетом
            if (($msgsock = socket_accept($sock)) < 0) {
                echo "Ошибка при старте соединений с сокетом\n";
            } else {
                echo "Сокет готов к приёму сообщений\n";
            }
            $msg = "Socket was successfully connected"; //Сообщение клиенту
            echo "Сообщение от сервера: $msg \n";
            socket_write($msgsock, $msg, strlen($msg)); //Запись в сокет
            // Бесконечный цикл ожидания клиентов
            do {
                $buf = socket_read($msgsock, 1024);//Читаем сообщение
                if (false === $buf) {
                    echo "Ошибка при чтении сообщения от клиента\n";       
                }
                if (empty($buf)) {
                    continue;
                }
                echo 'Сообщение от клиента: ' . $buf . "\n";
                $msg = "Message was successfully received"; //Сообщение клиенту
                echo "Сообщение от сервера: $msg \n";
                socket_write($msgsock, $msg, strlen($msg)); //Запись в сокет
                if ($buf == 'exit') {
                    echo "Отключаем клиент\n";
                    socket_close($msgsock);
                    break 1;
                } else {
                    call_user_func($handler, $buf);
                }
            } while (true);
        } while (true);
        //Останавливаем работу с сокетом
        if (isset($sock)) {
            socket_close($sock);
            echo "Сокет успешно закрыт\n";
        }
    }
}