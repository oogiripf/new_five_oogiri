class Watch {

    constructor ( win, startId, endId ) {
        this.win = win;
        this.startId = startId;
        this.endId = endId;

        this.startElem = document.getElementById( this.startId );
        this.endElem = document.getElementById( this.endId );
        this.flg = false;
    }

    watchStart() {
        let winHeight = this.win.outerHeight / 5 * 1;
        let startElemY = this.startElem.getBoundingClientRect().bottom;
        let endElemY = this.endElem.getBoundingClientRect().bottom - winHeight;

        if ( winHeight > startElemY &&  winHeight < endElemY ) {
            this.flg = true;
        } else {
            this.flg = false;
        }

        return this.flg;

    }

    log() {
        let winHeight = this.win.outerHeight / 5 * 4;
        let startElemY = this.startElem.getBoundingClientRect().bottom;
        let endElemY = this.endElem.getBoundingClientRect().bottom - winHeight;
        return [ this.flg, winHeight, startElemY, endElemY ];
    }

}

export { Watch };