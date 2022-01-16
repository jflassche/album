/**
 * Document handlers.
 */

document.onkeyup    = function(e) { handleKeyboard(e); };
window.onscroll     = function() { handleScroll() };

/**
 * Verwerk toetsenbord aanslagen.
 */
function handleKeyboard(e)
{
    back            = document.getElementById("back");
    first           = document.getElementById("first");
    last            = document.getElementById("last");
    next            = document.getElementById("next");
    previous        = document.getElementById("previous");
    
    switch (e.which)
    {
        case 27: // escape
            if (back)
            {
                window.location.replace(back.href);
            }
            break;

        case 35: // end
            if (last)
            {
                window.location.replace(last.href);
            }
            break;
            
        case 36: // home
            if (first)
            {
                window.location.replace(first.href);
            }
            break;
            
        case 37: // left
            if (previous)
            {
                window.location.replace(previous.href);
            }
            break;

        case 39: // right
            if (next)
            {
                window.location.replace(next.href);
            }
            break;
    }
    
    return;
}

/**
 * Toon knop pas na enig gescroll.
 */
function handleScroll()
{
    toTopButton     = document.getElementById("toTop");
    
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20)
    {
        toTopButton.style.display = "block";
    }
    else
    {
        toTopButton.style.display = "none";
    }

    return;
}

/**
 * Open menu div.
 */
function menuToggle()
{
    var toggleMenu  = document.getElementById("menuToggle");
    var menu        = document.getElementById("menu");
    var page        = document.getElementById("page");
    
    if (menu.classList.contains("js-menu--show"))
    {
        toggleMenu.innerHTML    = "&#9776;";
        menu.classList.remove("js-menu--show");
        menu.classList.add("js-menu--hide");
        page.style.marginLeft   = "0px";
    }
    else
    {
        toggleMenu.innerHTML    = "X";
        menu.classList.remove("js-menu--hide");
        menu.classList.add("js-menu--show");
        page.style.marginLeft   = "100px";
        
    }
    
    return;
}

/**
 * Ga naar de bovenkant van de pagina.
 */
function toTop()
{
    document.body.scrollTop = 0; // For Safari
    document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera

    return;
}