#include <SFML/Graphics.hpp>
#include <iostream>
#include <vector>
#include <QtWidgets>
#include <QPushButton>
#include <QLineEdit>
#include <QSize>
#include <QObject>
#include <QJsonDocument>
#include <QJsonArray>
#include <QJsonObject>
#include <QUrl>
#include <QtNetwork/QNetworkRequest>
#include <QtNetwork/QNetworkAccessManager>
#include <QNetworkReply>
#include <QByteArray>
#include <locale>
#include "Person.hpp"
#include "Platform.hpp"
#include "Hero.hpp"

using namespace std;
using namespace sf;

const int windowWidth = 1280;               //ширина и высота экрана
const int windowHeight = 700;

float accelGravity = 0.2f;
float maxGravity = 5.f;

QLineEdit *keyEdit;
QLineEdit *controlInfoEdit;
QWidget *loginWindow;

void GameStart()
{
    RenderWindow window(VideoMode(windowWidth , windowHeight), "Platformergame", Style::Close);        //создание полноэкранного окна с игрой
    window.setFramerateLimit(60);

    bool W, A, D;

    Texture HeroTexture;
    Texture platformsTexture;

    HeroTexture.loadFromFile("./playerhero3.png");                   //загрузка текстуры персонажа
    platformsTexture.loadFromFile("./iceplatform2.png");             //загрузка текстуры платформы

    int levelArray[40][40]={{0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1},
                            {1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0},
                            {0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,0,0,0},
                            {0,0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,0,0},          //создание карты, где 0 - пустота
                            {0,0,0,0,0,0,1,1,0,0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0},          //1 - платформа
                            {0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,1,1,0,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,0},
                            {0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0},
                            {0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0},
                            {0,0,0,0,0,0,0,0,0,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0},
                            {1,1,0,0,0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0},
                            {0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0},
                            {0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,1,1},
                            {0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,0,1,1,0},
                            {0,0,0,0,1,1,0,0,0,0,0,0,1,1,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,0,0,0,0},
                            {0,0,0,0,0,0,0,0,1,1,0,0,0,0,1,0,0,1,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0},
                            {0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0},
                            {0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0},
                            {0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0},
                            {0,0,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,1,0,0,0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0},
                            {0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,0,0,0,0,1,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0},
                            {0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0}
                            };

    vector <Platform> level;

    for(int i=0; i<40; i++){
        for(int j=0; j<40; j++){
            if(levelArray[i][j] == 1){
                Platform p(j*15, i*15, 15,15,platformsTexture);      //создание текстуры платформы
                level.push_back(p);
            }
        }
    }


   Hero Hero(-10,100,24,20, HeroTexture);                        //точка спавна персонажа и его размер

   View view(Vector2f(0.0f, 0.0f), Vector2f(windowWidth, windowHeight));

   float gameScale = 4;                                 //шкала зума карты
   view.zoom(1.0f/gameScale);

while (window.isOpen())
{
    Event event;
    while (window.pollEvent(event)){
    if (event.type == Event::Closed)
     window.close();
}
    Hero.update (W, A, D, level);
    view.setCenter(Hero.getPosition().x + Hero.size.x/2, Hero.getPosition().y + Hero.size.y/2);  //центрирование по позиции игрока

    W = Keyboard::isKeyPressed(Keyboard::W);                    //управление персонажем
    A = Keyboard::isKeyPressed(Keyboard::A);
    D = Keyboard::isKeyPressed(Keyboard::D);

    window.setView(view);
    window.clear(Color(150,218,243));                           //рисование цвет фона
    window.draw(Hero);                                              //рисование текстуры персонажа

for (Platform& p:level)
{
    window.draw(p);
    }
    window.display();
    }

};

void CheckCallBack(QNetworkReply*reply)                //функция парсинга ответа
{
    QJsonDocument jsonResponse = QJsonDocument::fromJson(reply->readAll());
    QJsonObject jsonObject = jsonResponse.object();
    int responseCode = jsonObject["Code"].toInt();
    if (responseCode == 200){                                               //при значении 200 отркывается игра
        loginWindow->close();
        GameStart();
    }
    else if(responseCode == 201){                                             //при значении 201 выводится сообщение об ошибке
        QMessageBox::warning(loginWindow, "Ошибка", "Введена неверная пара ключ-значение.");
    }
    else {
        QMessageBox::critical(loginWindow, "Ошибка", "Внимание! Произошла внутреняя ошибка! Сообщите об этом администратору");
    }
}

void CheckKey()                                         //произведение проверки ключа
{
    QJsonObject textdata;
    textdata["Action"] = "checkproductkey";
    textdata["ProductKey"] = keyEdit->text();
    textdata["ControlInfo"] = controlInfoEdit->text();

    QUrl url("http://83.220.173.39:8080/resources/scripts/producthandle.php");                  //ссылка на получение данных
    QNetworkRequest request(url);

    request.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");                  //устанавливает тип отправляемых данных в запросе на сервере

    QNetworkAccessManager *manager = new QNetworkAccessManager();                               //менеджер доступа

    QObject::connect(manager, &QNetworkAccessManager::finished, CheckCallBack);                 //менеджер подключения

    QJsonDocument document(textdata);
    QByteArray data = document.toJson();

    manager->post(request, data);
}

int main(int argc, char *argv[])
{
    QSize editSize(200,25);
    QSize buttonSize(100,20);               //размер кнопки

    QApplication app(argc, argv);
    loginWindow = new QWidget();            //создание окна логина

    loginWindow->setFixedSize(300, 160);        //фиксированный размер окна
    loginWindow->show();
    loginWindow->setWindowTitle("Авторизация");     //поле авторизации

    QPushButton *submitButton = new QPushButton(loginWindow);       //кнопка подтверждения
    submitButton->setText("Подтвердить");
    submitButton->move(100, 110);                       //координаты кнопки
    submitButton->setFixedSize(buttonSize);             //размер
    submitButton->show();

    keyEdit = new QLineEdit(loginWindow);               //поле для ключа
    keyEdit->setText("Введите ключ");
    keyEdit->move(50, 20);
    keyEdit->show();
    keyEdit->setFixedSize(editSize);

    controlInfoEdit = new QLineEdit(loginWindow);           //создание поля  логина
    controlInfoEdit->setText("Введите почту");
    controlInfoEdit->move(50, 70);
    controlInfoEdit->show();
    controlInfoEdit->setFixedSize(editSize);

    QObject::connect(submitButton,&QPushButton::clicked, CheckKey);             //проверка, нажата ли кнопка

    return app.exec();
}
