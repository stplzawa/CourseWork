#include <SFML/Graphics.hpp>
#include <iostream>
#include <vector>
#include "Person.hpp"
#include "Platform.hpp"
#include "Hero.hpp"
using namespace sf;

extern float accelGravity, maxGravity;

Hero::Hero(float X, float Y, float W, float H, Texture& t)
{
    speed = 2.3f;
    jumpHeight = 4.f;                               //свойства прыжка персонажа

    size.x=W;
    size.y=H;

    setTexture(t);
    setPosition(X,Y);
}

void Hero::update (bool &W, bool &A, bool &D, std::vector<Platform>& level)
{                                                                                       //обработка пржыка

    if(W && onGround) rate.y = jumpHeight * -1 ;

    if(onGround == false){
        rate.y+=accelGravity;
        if(rate.y > maxGravity) rate.y=maxGravity;
    }

    if(A) rate.x = -1.f;
    if(D) rate.x = 1.f;
    if(!(A or D)) rate.x = 0.f;

    move(rate.x*speed,0);
    colliding(rate.x, 0, level);

    onGround = false;
    move(0, rate.y);
    colliding(0, rate.y, level);

}

void Hero::colliding(float xvel, float yvel, std::vector<Platform>& level)
{                                                                                    //обработка коллизии платформ
    for(Platform& p:level)
    {
        if(getPosition().x+13.f<p.hitbox.right and getPosition().x+size.x-4.f > p.hitbox.left and
           getPosition().y < p.hitbox.bottom and getPosition().y+size.y > p.hitbox.top)
    {
        collision =true;
    }
    else {
        collision = false;
    }
if(collision)
{

    if(xvel>0){
        setPosition(p.hitbox.left - size.x+ 4.f, getPosition().y);
        rate.x =0.f;
    }
    if (xvel<0){
        setPosition(p.hitbox.right - 13.f, getPosition().y);
        rate.x=0.f;
    }
    if(yvel>0){
        setPosition(getPosition().x,p.hitbox.top - size.y +0.f);
        rate.y = 0.f;
        onGround=true;
    }
    if (yvel<0){
        setPosition(getPosition().x,p.hitbox.bottom - 0.f);
        rate.y=0.f;
    }

        }
    }
}
