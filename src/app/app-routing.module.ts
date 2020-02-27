import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { HomePageComponent } from './home-page/home-page.component';
import { CreateElectionComponent } from './create-election/create-election.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { SearchElectionsComponent } from './search-elections/search-elections.component';
import { AuthenticationService } from './authentication.service';
import { ManageElectionComponent } from './election-workspace/manage-election/manage-election.component';
import { ReportingComponent } from './election-workspace/reporting/reporting.component';
import { ElectionWorkspaceComponent } from './election-workspace/election-workspace.component';
import { BeatpathBallotCastComponent } from './election-workspace/beatpath/beatpath-ballot-cast/beatpath-ballot-cast.component';

// const appRoutes: Routes = [
//     { path: '', component: HomePageComponent},
//     { path: 'election_search', component: SearchElectionsComponent},
//     { path: 'create_election', component: CreateElectionComponent},
//     { path: 'manage_election/:election_name',
//         component: ManageElectionComponent,
//         // canActivateChild: [AuthenticationService],
//         children: [
//             { path: 'cast_vote', component: BeatpathBallotCastComponent},
//             { path: 'reporting', component: ReportingComponent}
//         ]
//     },
//     { path: '**', component: PageNotFoundComponent}
// ]

const appRoutes: Routes = [
    { path: '', component: HomePageComponent},
    { path: 'election_search', component: SearchElectionsComponent},
    { path: 'create_election', component: CreateElectionComponent},
    { path: ':election_name',
        component: ElectionWorkspaceComponent,
        // canActivateChild: [AuthenticationService],
        children: [
            { path: 'vote', component: BeatpathBallotCastComponent},
            { path: 'manage', component: ManageElectionComponent},
            { path: 'reporting', component: ReportingComponent}
        ]
    },
    { path: '**', component: PageNotFoundComponent}
]

@NgModule({
    imports: [RouterModule.forRoot(appRoutes, {useHash: false})],
    exports: [RouterModule]
})
export class AppRoutingModule { 

} 