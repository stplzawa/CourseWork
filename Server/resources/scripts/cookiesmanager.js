export default class CookiesManager{
    constructor(){
    }

    readCookie(CookieName){
        let AllCookies = this.getAllCookies();
        return AllCookies[CookieName] === undefined ? null : decodeURIComponent(AllCookies[CookieName]);
    }

    getAllCookies(Cookies = document.cookie){
        let CookiesArray = Cookies.split(";");
        let CookiesMap = {}
        CookiesArray.forEach(Pair => {
            Pair = Pair.split("=");
            CookiesMap[decodeURIComponent(Pair[0])] = decodeURIComponent(Pair[1]);
        });
        return CookiesMap;
    }

    setCookie(cookie, path = "/"){
        document.cookie = `${encodeURIComponent(cookie.name)}=${encodeURIComponent(cookie.value)}`;
    }

    deleteCookie(){
        // pass
    }
}