import { Component, OnInit } from '@angular/core';
import { UntilDestroy, untilDestroyed } from '@ngneat/until-destroy';
import { combineLatest, EMPTY } from 'rxjs';
import { tap } from 'rxjs/operators';

import { TransportDetector } from '../../interfaces/transport-detector';
import { TransportDetectorDataService } from '../../services/transport-detector-data/transport-detector-data.service';
import { TransportDetectorService } from '../../services/transport-detector/transport-detector.service';

@UntilDestroy()
@Component({
  selector: 'app-transport-detector-tabs',
  templateUrl: './transport-detector-tabs.component.html',
  styleUrls: ['./transport-detector-tabs.component.scss'],
})
export class TransportDetectorTabsComponent implements OnInit {
  public transportDetectorList: TransportDetector[] = [];
  public selectedItem: number;

  constructor(
    private transportDetectorService: TransportDetectorService,
    private transportDetectorDataService: TransportDetectorDataService,
  ) {}

  public ngOnInit(): void {
    combineLatest([this.transportDetectorService.getList(), this.transportDetectorService.getSelectedItem()])
      .pipe(
        tap(([transportDetectorList, selectedItem]) => {
          this.transportDetectorList = transportDetectorList;
          this.selectedItem = selectedItem;
        }),
        untilDestroyed(this),
      )
      .subscribe();
  }

  public onSelectedItem(id: number) {
    this.transportDetectorService.setSelectedItem(id);
  }
}
