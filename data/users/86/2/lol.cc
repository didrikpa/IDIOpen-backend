#include <iostream>
#include <string>
#include <cstdio>
#include <vector>

using namespace std;

int main(int argc, char* argv[])
{
	int a;

	fscanf(stdin, "%d", &a);

	for(int i = 0; i < a; ++i)
	{
		string str;
		cin >> str;
		
		if(str.find("lol") != string::npos)
		{
			cout << "0" << endl;
			continue;
		}

		if(str.find("ll") != string::npos ||
		   str.find("lo") != string::npos ||
		   str.find("ol") != string::npos)
		{
			cout << "1" << endl;
			continue;
		}

		size_t pos = str.find("l");
		
		if(pos != string::npos && str.length() > (pos+2) && str[pos+2] == 'l')
		{
			cout << "1" << endl;
			continue;
		}

		if(pos != string::npos ||
		   str.find('o') != string::npos)
		{
			cout << "2" << endl;
			continue;
		}

		cout << "3" << endl;
		continue;
	}

	return 0;
}
