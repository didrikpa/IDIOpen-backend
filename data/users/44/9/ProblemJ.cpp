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

int t, c, total;
vector<vector<int> > g;
int cost[201][201];
vector<int> rekke;
vector<int> dist;

void dijkstra(int);
int main() {
	scanf("%d", &t);
	while(t--) {
		scanf("%d", &c);
		rekke = vector<int> (c);
		g = vector<vector<int> > (c);
		for(int i = 0; i < c; i++)
			scanf("%d", &rekke[i]);
		for(int i = 0; i < c; i++) {
			for(int j = 0; j < c; j++) {
				scanf("%d", &cost[i][j]);
			}
		}
		bool ok = true; total = 0;
		for(int i = 0; ok && i < c-1; i++) {
			dist = vector<int> (c, -1);
			dijkstra(rekke[i]);
			ok &= dist[rekke[i+1]] != -1;
			total += dist[rekke[i+1]];
		}
		dist = vector<int> (c, -1);
		dijkstra(rekke[c-1]);
		ok &= dist[rekke[0]] != -1;
		total += dist[rekke[0]];

		if(!ok)
			printf("impossible\n");
		else
			printf("%d\n", total);

	}
	return 0;
}

void dijkstra(int start) {
	dist[start] = 0;
	priority_queue<pair<int, int> > pq;
	pq.push(MP(0, start));
	while(!pq.empty()) {
		int node = (pq.top()).second; pq.pop();
		for(int i = 0; i < c; i++) {
			if(cost[node][i] != -1 && dist[i] == -1) {
				dist[i] = dist[node] + cost[node][i];
				pq.push(MP((-1)*dist[i], i));
			}
			else if(cost[node][i] != -1 && dist[i] > dist[node]+cost[node][i]) {
				dist[i] = min(dist[i], dist[node]+cost[node][i]);
				pq.push(MP((-1)*dist[i], i));
			}
		}
	}
}

