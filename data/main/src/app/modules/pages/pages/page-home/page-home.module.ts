import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { TransportDetectorItemModule } from '../../../project/components/transport-detector-item/transport-detector-item.module';
import { TransportDetectorTabsModule } from '../../../project/components/transport-detector-tabs/transport-detector-tabs.module';
import { PageHomeComponent } from './page-home.component';

@NgModule({
  declarations: [PageHomeComponent],
  imports: [
    CommonModule,
    RouterModule.forChild([
      {
        path: '',
        component: PageHomeComponent,
      },
    ]),
    TransportDetectorTabsModule,
    TransportDetectorItemModule,
  ],
})
export class PageHomeModule {}
