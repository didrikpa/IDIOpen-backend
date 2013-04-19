/*
 * ProblemJ.cpp
 *
 *  Created on: Apr 13, 2013
 *      Author: ema069
 */

#include <cstdio>
#include <climits>
#include <cstdlib>
#include <cassert>
#include <cmath>
#include <cctype>
#include <cerrno>
#include <algorithm>
#include <list>
#include <queue>
#include <map>
#include <vector>
#include <cstring>
#include <iostream>
#include <string>
#include <string>
#include <iostream>

using namespace std;

#define REP(i,n) for(int _n=(n),i=0; i<_n; ++i)
#define FOR(i,a,b) for(int _b=(b),i=(a); i<=_b; ++i)
#define FORD(i,a,b) for(int _b=(b),i=(a); i>=_b; --i)

#define PB push_back
#define BEG begin()
#define END end()
#define SZ size()
#define MP make_pair
#define F first
#define S second
#define D double
#define LL long long
#define LD long double

int main() {
	int t;
	scanf("%d", &t);
	while(t--) {
		int workMin, reqMin, maxRow;
		scanf("%d%d%d", &workMin, &reqMin, &maxRow);
		bool ok = true;
		if(workMin - workMin / (maxRow+1) < reqMin){
			ok = false;
		}
			int verdi[workMin+1];
					for(int i = 1; i < workMin+1; i++){
						int val;
						scanf("%d", &val);
						verdi[i] = val;
					}
					int p[workMin+1][reqMin+1][maxRow+1];

					for(int i = 0; i < workMin+1; i++){
						for(int j = 0; j < reqMin+1; j++){
							for(int k = 0; k < maxRow+1; k++){
								if(i == 0 || j == 0 || k > j || j > i || k > i){
									p[i][j][k] = 0;
								} else if(k == 0){
									int best = 0;
									for(int l = 0; l < maxRow+1; l++){
										if(p[i-1][j][l] > best){
											best = p[i-1][j][l];
										}
									}
									p[i][j][k] = best;
								} else{
									p[i][j][k] = verdi[i]*k + p[i-1][j-1][k-1];
								}
							}
						}
					}
					int best = 0;
					for(int j = 0; j < workMin+1; j++){
						for(int k = 0; k < maxRow+1; k++){
							if(p[j][reqMin][k] > best){
								best = p[j][reqMin][k];
							}
						}
					}
					if(ok) printf("%d\n", best);
					else printf("impossible\n");



	}


	return 0;
}

