import '@babel/polyfill';
import { Promise } from 'core-js';

let promise = new Promise(( resolve, reject ) => {
    setTimeout(()=>{
        let msg = 'count 0';
        return resolve( msg );
    }, 3000 );
});

promise.then(( m )=>{
    console.log( m );
    return m;
}).then(( m )=>{
    m += ' message added.';
    return m;
}).then(( m )=>{
    console.log( m );
});