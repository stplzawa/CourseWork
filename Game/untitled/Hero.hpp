#pragma once
#include <SFML/Graphics.hpp>
#include <iostream>
#include <vector>
#include "Person.hpp"
#include "Platform.hpp"
using namespace sf;

class Hero : public Person
{
    private:
        float speed, jumpHeight;
        bool collision, onGround;

    public:
        Hero(float X, float Y, float W, float H, Texture& t);
        void update(bool &W, bool &A, bool &D, std::vector<Platform>& level);
        void colliding(float xvel, float yvel, std::vector<Platform>& level);
};
