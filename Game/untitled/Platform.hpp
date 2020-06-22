#pragma once
#include <SFML/Graphics.hpp>
#include <iostream>
#include "Person.hpp"
using namespace sf;

class Platform : public Person
{
    public:
        Platform(float X, float Y, float W, float H, Texture& t);
};

