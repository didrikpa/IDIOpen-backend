import java.util.Scanner;
import java.util.Iterator;
import java.util.LinkedList;

public class Solver {
    public static void main(String[] args) {
        Scanner input = new Scanner(System.in);

        int T = input.nextInt();
        input.nextLine();

        for (int i = 0; i < T; ++i) {
            int N = input.nextInt();
            int distance = input.nextInt();
            input.nextLine();

            LinkedList<Double> finishingTimes = new LinkedList<Double>();
            for (int startingTime = 0; startingTime < N; ++startingTime) {
                int speed = input.nextInt();
                double finishingTime = startingTime + (double)distance / speed;

                finishingTimes.add(finishingTime);
            }
            input.nextLine();

            int numRaces;
            for (numRaces = 0; !finishingTimes.isEmpty(); ++numRaces) {
                double earliestFinishingTime = -1;
                Iterator<Double> it = finishingTimes.iterator();

                while (it.hasNext()) {
                    double finishingTime = it.next();

                    if (finishingTime > earliestFinishingTime) {
                        earliestFinishingTime = finishingTime;

                        it.remove();
                    }
                }
            }

            System.out.println(numRaces);
        }
    }
}
