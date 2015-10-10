$(function(){
    
    // the 2 following js var are generated from PlaygroundUser\View\Helper\FacebookLogin
    var APP_ID =  FbDomainAuthId;
    var APP_SCOPE = FbDomainAuthScope;
    
    /**** Facebook init */
    window.fbAsyncInit = function() {
        FB.init({
          appId      : APP_ID,
          version    : 'v2.5',
          status     : true, // check the login status upon init?
          cookie     : true, // set sessions cookies to allow your server to access the session?
          xfbml      : true  // parse XFBML tags on this page?
        });

        FB.Canvas.setAutoGrow();
        FB.Canvas.getPageInfo(function (pageInfo) {
            $({y: pageInfo.scrollTop}).animate(
                {y: 0},
                {
                    duration: 0,
                    step: function (offset)
                    {
                        FB.Canvas.scrollTo(0, offset);
                    }
                }
            );
        });
    };

    (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
    
    $('#fb-play').click(function(event){
        event.preventDefault();
        _this = $(this);
        FB.login(function(response) {
            if (response.authResponse) {
                //user just authorized your app
                $('#fb-play').style.display = 'none';
                var uid = response.authResponse.userID;
                var access_token = response.authResponse.accessToken;
                window.location = _this.find('a').attr('href');
                getUserData();
            }
        }, {scope: 'email,public_profile', return_scopes: true});      
        return false;
    });

    function getUserData() {
        FB.api('/me?fields=id,birthday,name,first_name,last_name,link,website,gender,locale,about,email,hometown,location', function(response) {
            $('#response').innerHTML = 'Hello ' + response.name;
        });
    }
});