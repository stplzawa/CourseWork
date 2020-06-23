QT += core gui
QT += network

greaterThan(QT_MAJOR_VERSION, 4): QT += widgets

CONFIG += c++11
DEFINES += QT_DEPRECATED_WARNINGS

SOURCES += \
    game.cpp \
    hero.cpp \
    platform.cpp


INCLUDEPATH += $$PWD/SFML/include
DEPENDPATH += $$PWD/SFML/include

win32:CONFIG(release, debug|release): LIBS += -L$$PWD/SFML/FOR_QT/LIBS/ -lsfml-audio -lsfml-graphics -lsfml-main -lsfml-network -lsfml-window -lsfml-system
else:win32:CONFIG(debug, debug|release): LIBS += -L$$PWD/SFML/FOR_QT/LIBS/  -lsfml-audio-d -lsfml-graphics-d -lsfml-main-d -lsfml-network-d -lsfml-window-d -lsfml-system-d

TRANSLATIONS += \
    untitled_en_150.ts

qnx: target.path = /tmp/$${TARGET}/bin
else: unix:!android: target.path = /opt/$${TARGET}/bin
!isEmpty(target.path): INSTALLS += target


HEADERS += \
    Hero.hpp \
    Hitbox.hpp \
    Person.hpp \
    Platform.hpp
