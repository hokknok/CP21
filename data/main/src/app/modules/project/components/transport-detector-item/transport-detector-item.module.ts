import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { GraphLineDynamicsModule } from '../graph-line-dynamics/graph-line-dynamics.module';
import { GraphLineModule } from '../graph-line/graph-line.module';
import { TransportDetectorItemComponent } from './transport-detector-item.component';

@NgModule({
  declarations: [TransportDetectorItemComponent],
  exports: [TransportDetectorItemComponent],
  imports: [CommonModule, GraphLineModule, GraphLineDynamicsModule],
})
export class TransportDetectorItemModule {}
