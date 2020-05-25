class LoggerUtil {
    constructor(prefix, style){
        this.prefix = `%c[${prefix ? prefix : 'Application'}]`;
        this.style = style ? style : 'color:#e13d3a;font-weight:bold;';
    }

    log(){
        console.log.apply(null, [this.prefix, this.style, ...arguments])
    }

    info(){
        console.info.apply(null, [this.prefix, this.style, ...arguments])
    }

    warn(){
        console.warn.apply(null, [this.prefix, this.style, ...arguments])
    }

    debug(){
        console.debug.apply(null, [this.prefix, this.style, ...arguments])
    }

    error(){
        console.error.apply(null, [this.prefix, this.style, ...arguments])
    }
}

export default (prefix, style) => {
    return new LoggerUtil(prefix, style);
};
