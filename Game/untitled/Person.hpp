#pragma once
#include <SFML/Graphics.hpp>
#include "Hitbox.hpp"
using namespace sf;

class Person : public Sprite
{
    public:
        Vector2f rate, size;
        Hitbox hitbox;
};

