import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TransportDetectorTabsComponent } from './transport-detector-tabs.component';

@NgModule({
  declarations: [TransportDetectorTabsComponent],
  exports: [TransportDetectorTabsComponent],
  imports: [CommonModule],
})
export class TransportDetectorTabsModule {}
