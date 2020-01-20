import { Injectable } from '@angular/core';
import { election } from '../shared/election.model';

@Injectable({
  providedIn: 'root'
})
export class ManageElectionService{
  election: election;

  constructor() { }
}