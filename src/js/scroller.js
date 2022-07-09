class scroller {
    constructor( win, doc, cls ) {
        this.win = win;
        this.doc = doc;
        this.cls = cls;

        this.addClass();
        this.start();
    }

    addClass() {
        let winHeight = this.win.outerHeight / 5 * 4;
        this.cls.forEach( classes => {
            let elems = this.doc.getElementsByClassName( classes );
            this.win.addEventListener( 'scroll', function() {
                [...elems].forEach(( elem )=>{
                    if ( elem.getBoundingClientRect().top + window.pageYOffset < window.pageYOffset + winHeight ) {
                        elem.classList.add( 'active' );
                    }
                });
            });
        });
    }

    start() {
        let winHeight = this.win.outerHeight / 5 * 4;
        this.cls.forEach( classes => {
            let elems = this.doc.getElementsByClassName( classes );
            this.win.addEventListener( 'DOMContentLoaded', function() {
                [...elems].forEach(( elem )=>{
                    if ( elem.getBoundingClientRect().top < window.pageYOffset + winHeight ) {
                        elem.classList.add( 'active' );
                    }
                });
            });
        });
    }

}

module.exports = scroller;