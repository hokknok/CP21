// export type GraphDataItem = Record<number, number>;
export interface GraphDataItem {
  date: string;
  value: number;
}

export interface GraphDataTrendItem {
  date: string;
  value: boolean;
}

export interface GraphDataMap {
  date: string[];
  value: number[];
}
