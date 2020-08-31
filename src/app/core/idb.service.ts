import { Injectable } from '@angular/core';
import { CoreModule } from './core.module';
import { Observable, Subject } from 'rxjs';
import { openDB, DBSchema, IDBPDatabase } from 'c:/Users/danny/Documents/voting-web-calculator/node_modules/idb/build/esm/index';
// ./idb/build/esm/index

interface MyDB extends DBSchema {
  'rcv.vote': {
    key: string,
    value: string
  }
}

@Injectable({
  providedIn: CoreModule
})
export class IdbService {
  private _dataChange: Subject<string> = new Subject<string>();
  private db: Promise<IDBPDatabase<MyDB>>;

  constructor() {
  }

  connect_to_idb(){
    this.db = openDB<MyDB>('rcv.vote', 1, 
      {upgrade: (db) => {
        db.createObjectStore("rcv.vote", {keyPath: 'cookie'});
        }
      } 
    );
  }
  
  add_cookie(value: string){
    this.db.then((db: IDBPDatabase<MyDB>) => {
        const tx = db.transaction('rcv.vote',"readwrite");
        tx.objectStore('rcv.vote').put(value);
        this.get_cookie().then((cookie: string) => {
            this._dataChange.next(cookie);
        });
        tx.done;
    });
  }
  
  delete_cookie() {
    this.db.then((db: IDBPDatabase<MyDB>) => {
        db.delete('rcv.vote','cookie');
    })
  }

  get_cookie(){
    return this.db.then((db: IDBPDatabase<MyDB>) => {
        const tx = db.transaction('rcv.vote','readonly');
        const store = tx.objectStore('rcv.vote');
        return store.get('cookie');
    })
  }
  
  dataChanged(): Observable<any> {
    return this._dataChange;
  }
}
