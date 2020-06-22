#include <SFML/Graphics.hpp>
#include <iostream>
#include "Person.hpp"
#include "Platform.hpp"

using namespace sf;

Platform::Platform(float X, float Y, float W, float H, Texture& t)
{
    size.x=W;
    size.y=H;

    hitbox.left = X + 0.f;              //хитбокс границ
    hitbox.right = X+size.x - 0.f;
    hitbox.top = Y + 0.f;
    hitbox.bottom = Y+size.y - 0.f;

    setTexture(t);
    setPosition(X,Y);
}
