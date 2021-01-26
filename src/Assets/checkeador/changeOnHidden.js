if (typeof window['trackChange'] !== 'function') {
    /**
     * Thanks to https://stackoverflow.com/users/474597/lulalala
     * 
     * https://stackoverflow.com/a/31719339/9229515
     * 
     * Use like this
     * 
     *  trackChange($("input[name=foo]")[0]);
     * 
     */
    MutationObserver = window.MutationObserver ||
        window.WebKitMutationObserver ||
        window.MozMutationObserver;

    function trackChange(element) {
        if (isNode(element)) {
            var observer = new MutationObserver(function(mutations, observer) {
                if (mutations[0].attributeName == "value") {
                    $(element).trigger("change");
                }
            });
            observer.observe(element, {
                attributes: true
            });
        } else if (typeof element === "array") {
            $(element).each(function(index, ele) {
                trackChange(ele);
            });
        } else if (element instanceof jQuery) {
            if (element.length > 1) {
                element.each(function(index, ele) {
                    trackChange(ele);
                });
            } else {
                trackChange(element[0]);
            }
        } else {
            console.log("is not possible to track" + typeof element, element);
        }
    }

    /**
     * Thanks to https://stackoverflow.com/users/36866/some
     * 
     * https://stackoverflow.com/a/384380/9229515
     * 
     * 
     */
    //Returns true if it is a DOM node
    function isNode(o) {
        return (
            typeof Node === "object" ? o instanceof Node :
            o && typeof o === "object" && typeof o.nodeType === "number" && typeof o.nodeName === "string"
        );
    }

    //Returns true if it is a DOM element    
    function isElement(o) {
        return (
            typeof HTMLElement === "object" ? o instanceof HTMLElement : //DOM2
            o && typeof o === "object" && o !== null && o.nodeType === 1 && typeof o.nodeName === "string"
        );
    }
}